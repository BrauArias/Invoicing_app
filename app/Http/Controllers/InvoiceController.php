<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private PdfService $pdfService,
    ) {}

    public function index(Request $request, Company $company): Response
    {
        $query = Invoice::where('company_id', $company->id)
            ->with('client:id,name');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters'  => $request->only(['status', 'type', 'search']),
        ]);
    }

    public function create(Request $request, Company $company): Response
    {
        $clients  = $company->clients()->orderBy('name')->get(['id', 'name', 'nif', 'irpf_applicable', 'irpf_rate']);
        $products = $company->products()->where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'description', 'unit_price', 'vat_rate', 'unit']);

        return Inertia::render('Invoices/Create', [
            'clients'  => $clients,
            'products' => $products,
            'defaults' => [
                'issue_date' => now()->format('Y-m-d'),
                'due_date'   => now()->addDays(30)->format('Y-m-d'),
                'vat_rate'   => (float) $company->default_vat_rate,
                'irpf_rate'  => (float) $company->irpf_rate,
            ],
        ]);
    }

    public function store(Request $request, Company $company)
    {
        $data = $request->validate([
            'client_id'      => ['required', 'integer', 'exists:clients,id'],
            'type'           => ['required', 'in:invoice,proforma,quote,credit_note'],
            'status'         => ['required', 'in:draft,sent'],
            'issue_date'     => ['required', 'date'],
            'due_date'       => ['nullable', 'date'],
            'service_date'   => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'payment_terms'  => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
            'lines'          => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity'    => ['required', 'numeric', 'min:0.001'],
            'lines.*.unit'        => ['required', 'string'],
            'lines.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'lines.*.discount'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.vat_rate'    => ['required', 'numeric', 'in:0,4,10,21'],
            'lines.*.product_id'  => ['nullable', 'integer'],
        ]);

        $invoice = $this->invoiceService->create($company, $data);

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Factura creada correctamente.');
    }

    public function show(Company $company, Invoice $invoice): Response
    {
        $this->authorizeInvoice($company, $invoice);
        $invoice->load(['lines', 'client', 'payments']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function emit(Company $company, Invoice $invoice)
    {
        $this->authorizeInvoice($company, $invoice);
        $this->invoiceService->emit($invoice);

        return back()->with('success', "Factura {$invoice->full_number} emitida.");
    }

    public function markPaid(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeInvoice($company, $invoice);

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => $request->get('paid_at', now()),
        ]);

        return back()->with('success', 'Factura marcada como pagada.');
    }

    public function download(Company $company, Invoice $invoice)
    {
        $this->authorizeInvoice($company, $invoice);

        return $this->pdfService->download($invoice);
    }

    public function duplicate(Company $company, Invoice $invoice)
    {
        $this->authorizeInvoice($company, $invoice);
        $invoice->load('lines');

        $newInvoice = $this->invoiceService->create($company, [
            'client_id'      => $invoice->client_id,
            'type'           => $invoice->type,
            'status'         => 'draft',
            'issue_date'     => now()->format('Y-m-d'),
            'due_date'       => now()->addDays(30)->format('Y-m-d'),
            'payment_method' => $invoice->payment_method,
            'payment_terms'  => $invoice->payment_terms,
            'notes'          => $invoice->notes,
            'lines'          => $invoice->lines->map(fn($l) => [
                'product_id'  => $l->product_id,
                'description' => $l->description,
                'quantity'    => $l->quantity,
                'unit'        => $l->unit,
                'unit_price'  => $l->unit_price,
                'discount'    => $l->discount,
                'vat_rate'    => $l->vat_rate,
            ])->toArray(),
        ]);

        return redirect()->route('invoices.show', $newInvoice->id)
            ->with('success', 'Factura duplicada como borrador.');
    }

    public function cancel(Company $company, Invoice $invoice)
    {
        $this->authorizeInvoice($company, $invoice);
        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Factura cancelada.');
    }

    private function authorizeInvoice(Company $company, Invoice $invoice): void
    {
        if ($invoice->company_id !== $company->id) {
            abort(403);
        }
    }
}
