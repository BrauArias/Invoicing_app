<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    /**
     * Genera el PDF de una factura y lo guarda en storage.
     * Devuelve la ruta relativa guardada.
     */
    public function generate(Invoice $invoice): string
    {
        $company  = $invoice->company_snapshot ?? $invoice->company->toArray();
        $template = $company['invoice_template'] ?? 'classic';
        $view     = "pdf.invoice-{$template}";

        // Convertir logo a base64 para embeber en el PDF
        $logoBase64 = null;
        $logoPath = $company['logo_path'] ?? null;
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $logoContent = Storage::disk('public')->get($logoPath);
            $extension   = pathinfo($logoPath, PATHINFO_EXTENSION);
            $mimeType    = match(strtolower($extension)) {
                'png'        => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'svg'        => 'image/svg+xml',
                default      => 'image/png',
            };
            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
        }

        $invoice->load(['lines', 'client']);

        $html = view($view, [
            'invoice'     => $invoice,
            'company'     => $company,
            'logoBase64'  => $logoBase64,
            'primaryColor' => $company['primary_color'] ?? '#1e3a5f',
            'accentColor'  => $company['accent_color']  ?? '#d4a017',
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
            ]);

        $path = 'invoices/' . $invoice->full_number . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Genera el PDF y devuelve la respuesta HTTP para descarga directa.
     */
    public function download(Invoice $invoice): \Illuminate\Http\Response
    {
        $company  = $invoice->company_snapshot ?? $invoice->company->toArray();
        $template = $company['invoice_template'] ?? 'classic';
        $view     = "pdf.invoice-{$template}";

        $logoBase64 = null;
        $logoPath   = $company['logo_path'] ?? null;
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $logoContent = Storage::disk('public')->get($logoPath);
            $mimeType    = str_contains($logoPath, '.png') ? 'image/png' : 'image/jpeg';
            $logoBase64  = 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
        }

        $invoice->load(['lines', 'client']);

        $html = view($view, [
            'invoice'      => $invoice,
            'company'      => $company,
            'logoBase64'   => $logoBase64,
            'primaryColor' => $company['primary_color'] ?? '#1e3a5f',
            'accentColor'  => $company['accent_color']  ?? '#d4a017',
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
            ]);

        return $pdf->download($invoice->full_number . '.pdf');
    }
}
