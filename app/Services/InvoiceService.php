<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Calcula los totales de una factura a partir de las líneas.
     * Devuelve los totales y el desglose de IVA.
     */
    public function calculateTotals(array $lines, float $irpfRate = 0, bool $irpfApplicable = false): array
    {
        $subtotal    = 0;
        $vatByRate   = [];

        foreach ($lines as $line) {
            $qty        = (float) ($line['quantity']   ?? 1);
            $price      = (float) ($line['unit_price'] ?? 0);
            $discount   = (float) ($line['discount']   ?? 0);
            $vatRate    = (float) ($line['vat_rate']   ?? 21);

            $lineSubtotal = round($qty * $price * (1 - $discount / 100), 2);
            $lineVat      = round($lineSubtotal * $vatRate / 100, 2);

            $subtotal += $lineSubtotal;

            if (!isset($vatByRate[$vatRate])) {
                $vatByRate[$vatRate] = ['rate' => $vatRate, 'base' => 0, 'amount' => 0];
            }
            $vatByRate[$vatRate]['base']   += $lineSubtotal;
            $vatByRate[$vatRate]['amount'] += $lineVat;
        }

        $subtotal  = round($subtotal, 2);
        $vatAmount = round(array_sum(array_column($vatByRate, 'amount')), 2);
        $irpfAmount = $irpfApplicable ? round($subtotal * $irpfRate / 100, 2) : 0;
        $total     = round($subtotal + $vatAmount - $irpfAmount, 2);

        // Round bases in breakdown
        foreach ($vatByRate as &$vb) {
            $vb['base']   = round($vb['base'], 2);
            $vb['amount'] = round($vb['amount'], 2);
        }

        return [
            'subtotal'      => $subtotal,
            'vat_amount'    => $vatAmount,
            'irpf_amount'   => $irpfAmount,
            'total'         => $total,
            'vat_breakdown' => array_values($vatByRate),
        ];
    }

    /**
     * Asigna el número de factura de forma secuencial y atómica.
     * Garantiza que no haya saltos en la numeración (requisito legal RD 1619/2012).
     */
    public function assignNumber(Invoice $invoice, Company $company): void
    {
        DB::transaction(function () use ($invoice, $company) {
            // Bloquear la fila de la empresa para evitar concurrencia
            $company = Company::lockForUpdate()->find($company->id);

            $currentYear = (int) date('Y');
            if ($company->invoice_year !== $currentYear) {
                $company->invoice_counter = 1;
                $company->invoice_year    = $currentYear;
            }

            $series     = $invoice->series ?? $company->invoice_series;
            $number     = $company->invoice_counter;
            $fullNumber = sprintf('%s%d-%03d', $series, $currentYear, $number);

            $invoice->series      = $series;
            $invoice->number      = $number;
            $invoice->full_number = $fullNumber;
            $invoice->save();

            $company->invoice_counter += 1;
            $company->save();
        });
    }

    /**
     * Crea una factura completa con sus líneas, calcula totales y asigna número.
     */
    public function create(Company $company, array $data): Invoice
    {
        return DB::transaction(function () use ($company, $data) {
            $client = $company->clients()->findOrFail($data['client_id']);

            // Determinar si aplica IRPF
            $irpfApplicable = $client->irpf_applicable || $company->irpf_applicable;
            $irpfRate       = $client->irpf_applicable
                ? (float) $client->irpf_rate
                : (float) $company->irpf_rate;

            $lines   = $data['lines'] ?? [];
            $totals  = $this->calculateTotals($lines, $irpfRate, $irpfApplicable);

            $series = match($data['type'] ?? 'invoice') {
                'credit_note' => $company->rectification_series,
                'quote'       => $company->quote_series,
                default       => $company->invoice_series,
            };

            $invoice = Invoice::create([
                'company_id'      => $company->id,
                'client_id'       => $client->id,
                'type'            => $data['type']    ?? 'invoice',
                'status'          => $data['status']  ?? 'draft',
                'series'          => $series,
                'number'          => 0,
                'full_number'     => 'TEMP',
                'issue_date'      => $data['issue_date'],
                'due_date'        => $data['due_date']     ?? null,
                'service_date'    => $data['service_date'] ?? null,
                'client_name'     => $client->name,
                'client_nif'      => $client->nif,
                'client_address'  => $client->address . ($client->city ? ', ' . $client->city : ''),
                'client_city'     => $client->city,
                'client_postal_code' => $client->postal_code,
                'client_country'  => $client->country,
                'subtotal'        => $totals['subtotal'],
                'vat_amount'      => $totals['vat_amount'],
                'irpf_amount'     => $totals['irpf_amount'],
                'total'           => $totals['total'],
                'vat_breakdown'   => $totals['vat_breakdown'],
                'payment_method'  => $data['payment_method']  ?? null,
                'payment_terms'   => $data['payment_terms']   ?? null,
                'notes'           => $data['notes']            ?? null,
                'internal_notes'  => $data['internal_notes']  ?? null,
                'currency'        => 'EUR',
                'related_invoice_id' => $data['related_invoice_id'] ?? null,
            ]);

            // Crear líneas
            foreach ($lines as $position => $lineData) {
                $qty      = (float) ($lineData['quantity']   ?? 1);
                $price    = (float) ($lineData['unit_price'] ?? 0);
                $discount = (float) ($lineData['discount']   ?? 0);
                $vatRate  = (float) ($lineData['vat_rate']   ?? 21);

                $lineSubtotal = round($qty * $price * (1 - $discount / 100), 2);
                $lineVat      = round($lineSubtotal * $vatRate / 100, 2);

                InvoiceLine::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $lineData['product_id'] ?? null,
                    'position'    => $position + 1,
                    'description' => $lineData['description'],
                    'quantity'    => $qty,
                    'unit'        => $lineData['unit'] ?? 'servicio',
                    'unit_price'  => $price,
                    'discount'    => $discount,
                    'vat_rate'    => $vatRate,
                    'subtotal'    => $lineSubtotal,
                    'vat_amount'  => $lineVat,
                    'total'       => $lineSubtotal + $lineVat,
                ]);
            }

            // Asignar número solo si no es borrador
            if ($invoice->status !== 'draft') {
                $this->assignNumber($invoice, $company);
            }

            return $invoice->fresh(['lines', 'client']);
        });
    }

    /**
     * Emitir una factura borrador: asigna número y guarda snapshot de empresa.
     */
    public function emit(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'draft') {
            return $invoice;
        }

        $company = $invoice->company;

        // Snapshot completo de la empresa en el momento de emisión
        $snapshot = $company->toArray();
        $snapshot['logo_url'] = $company->logo_url;

        $invoice->company_snapshot = $snapshot;
        $invoice->status = 'sent';
        $invoice->save();

        $this->assignNumber($invoice, $company);

        return $invoice->fresh();
    }
}
