<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #333; background: #fff; }

    /* Sidebar layout via absolute positioning (DomPDF-compatible) */
    .sidebar {
        position: absolute;
        top: 0; left: 0;
        width: 55mm;
        min-height: 297mm;
        background: {{ $company['primary_color'] ?? '#1e3a5f' }};
        padding: 12mm 6mm 10mm 6mm;
    }
    .content {
        margin-left: 60mm;
        padding: 12mm 12mm 10mm 6mm;
    }

    /* Sidebar elements */
    .sb-logo img { max-width: 90px; max-height: 50px; }
    .sb-company-name { font-size: 11pt; font-weight: bold; color: #fff; margin-top: 4mm; word-wrap: break-word; }
    .sb-company-sub { font-size: 7.5pt; color: rgba(255,255,255,0.75); margin-top: 1mm; line-height: 1.4; word-wrap: break-word; }
    .sb-divider { border: none; border-top: 1px solid rgba(255,255,255,0.3); margin: 4mm 0; }
    .sb-section-title { font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5pt;
                        color: rgba(255,255,255,0.6); margin-bottom: 2mm; }
    .sb-text { font-size: 8pt; color: #fff; line-height: 1.5; word-wrap: break-word; }
    .sb-accent { color: {{ $company['accent_color'] ?? '#d4a017' }}; font-weight: bold; }

    /* Invoice header */
    .inv-type { font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1pt;
                color: {{ $company['primary_color'] ?? '#1e3a5f' }}; margin-bottom: 1mm; }
    .inv-number { font-size: 20pt; font-weight: bold; color: #222; line-height: 1; }
    .inv-draft { font-size: 16pt; font-weight: bold; color: #bbb; font-style: italic; }

    /* Date table */
    .date-table { width: 100%; border-collapse: collapse; margin: 4mm 0; }
    .date-table td { font-size: 8.5pt; padding: 2px 0; }
    .date-table .label { color: #999; width: 35%; }
    .date-table .value { font-weight: 500; color: #333; }

    /* Client section */
    .to-label { font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5pt;
                color: #999; margin-bottom: 2mm; margin-top: 4mm; }
    .client-name { font-size: 11pt; font-weight: bold; color: #222; margin-bottom: 1mm; }
    .client-detail { font-size: 8.5pt; color: #666; line-height: 1.5; }

    /* Accent line */
    .accent-line { border: none; border-top: 3px solid {{ $company['accent_color'] ?? '#d4a017' }}; margin: 5mm 0; }

    /* Lines table */
    .lines-table { width: 100%; border-collapse: collapse; }
    .lines-table thead tr { border-bottom: 2px solid {{ $company['primary_color'] ?? '#1e3a5f' }}; }
    .lines-table thead th { padding: 3px 4px 5px 4px; font-size: 8pt; color: {{ $company['primary_color'] ?? '#1e3a5f' }};
                            font-weight: bold; text-align: left; }
    .lines-table thead th.right { text-align: right; }
    .lines-table thead th.center { text-align: center; }
    .lines-table tbody tr { border-bottom: 1px solid #f0f0f0; }
    .lines-table tbody td { padding: 4px 4px; font-size: 9pt; vertical-align: top; }
    .lines-table tbody td.right { text-align: right; }
    .lines-table tbody td.center { text-align: center; color: #888; }
    .line-desc { font-weight: 500; color: #222; }
    .line-unit { font-size: 7.5pt; color: #aaa; }

    /* Totals */
    .totals-outer { width: 100%; border-collapse: collapse; margin-top: 4mm; }
    .totals-outer td { vertical-align: top; }
    .totals-spacer { width: 50%; }
    .totals-box { width: 50%; }
    .totals-inner { width: 100%; border-collapse: collapse; }
    .totals-inner td { padding: 3px 0; font-size: 9pt; }
    .totals-inner .label { color: #777; }
    .totals-inner .amount { text-align: right; }
    .totals-inner .total-label { font-size: 10pt; font-weight: bold; color: #222; border-top: 2px solid {{ $company['primary_color'] ?? '#1e3a5f' }}; padding-top: 4px; }
    .totals-inner .total-amount { font-size: 13pt; font-weight: bold; color: {{ $company['accent_color'] ?? '#d4a017' }};
                                   text-align: right; border-top: 2px solid {{ $company['primary_color'] ?? '#1e3a5f' }}; padding-top: 4px; }
    .totals-inner .irpf { color: #c0392b; }

    /* Notes & payment */
    .info-table { width: 100%; border-collapse: collapse; margin-top: 5mm; }
    .info-cell { vertical-align: top; font-size: 8.5pt; }
    .info-title { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; color: #aaa; margin-bottom: 2px; }
    .info-text { color: #555; line-height: 1.5; }

    .footer { font-size: 7.5pt; color: #bbb; text-align: center; margin-top: 6mm; border-top: 1px solid #eee; padding-top: 3mm; }
</style>
</head>
<body>

{{-- Sidebar --}}
<div class="sidebar">
    {{-- Logo --}}
    @if(!empty($company['logo_base64']))
        <div class="sb-logo"><img src="data:image/png;base64,{{ $company['logo_base64'] }}" alt="{{ $company['name'] }}"></div>
    @endif

    <div class="sb-company-name">{{ $company['name'] }}</div>
    @if(!empty($company['trade_name']) && $company['trade_name'] !== $company['name'])
        <div class="sb-company-sub">{{ $company['trade_name'] }}</div>
    @endif

    <hr class="sb-divider">

    <div class="sb-section-title">Emisor</div>
    @if(!empty($company['nif']))<div class="sb-text">NIF: {{ $company['nif'] }}</div>@endif
    @if(!empty($company['address']))<div class="sb-text">{{ $company['address'] }}</div>@endif
    @if(!empty($company['city']))<div class="sb-text">{{ $company['postal_code'] ?? '' }} {{ $company['city'] }}</div>@endif
    @if(!empty($company['province']))<div class="sb-text">{{ $company['province'] }}</div>@endif
    @if(!empty($company['phone']))<div class="sb-text" style="margin-top:2mm;">{{ $company['phone'] }}</div>@endif
    @if(!empty($company['email']))<div class="sb-text">{{ $company['email'] }}</div>@endif
    @if(!empty($company['website']))<div class="sb-text">{{ $company['website'] }}</div>@endif

    @if(!empty($company['show_bank_details']) && !empty($company['iban']))
        <hr class="sb-divider">
        <div class="sb-section-title">Datos bancarios</div>
        <div class="sb-text" style="font-size:7.5pt;">{{ $company['iban'] }}</div>
        @if(!empty($company['swift']))<div class="sb-text" style="font-size:7.5pt;">SWIFT: {{ $company['swift'] }}</div>@endif
    @endif
</div>

{{-- Main content --}}
<div class="content">

    {{-- Invoice header --}}
    <div class="inv-type">
        @switch($invoice->type)
            @case('proforma') Factura Proforma @break
            @case('quote') Presupuesto @break
            @case('credit_note') Factura Rectificativa @break
            @default Factura @break
        @endswitch
    </div>

    @if($invoice->full_number)
        <div class="inv-number">{{ $invoice->full_number }}</div>
    @else
        <div class="inv-draft">BORRADOR</div>
    @endif

    <table class="date-table">
        <tr>
            <td class="label">Fecha</td>
            <td class="value">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
        </tr>
        @if($invoice->due_date)
        <tr>
            <td class="label">Vencimiento</td>
            <td class="value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
        </tr>
        @endif
        @if($invoice->paid_at)
        <tr>
            <td class="label" style="color:#27ae60;">Cobrada</td>
            <td class="value" style="color:#27ae60;">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d/m/Y') }}</td>
        </tr>
        @endif
        @if($invoice->payment_method)
        <tr>
            <td class="label">Pago</td>
            <td class="value">{{ ucfirst($invoice->payment_method) }}</td>
        </tr>
        @endif
    </table>

    {{-- Client --}}
    <div class="to-label">Facturado a</div>
    <div class="client-name">{{ $invoice->client_name }}</div>
    @if($invoice->client_nif)<div class="client-detail">NIF: {{ $invoice->client_nif }}</div>@endif
    @if($invoice->client_address)<div class="client-detail">{{ $invoice->client_address }}</div>@endif
    @if($invoice->client_city)<div class="client-detail">{{ $invoice->client_postal_code ?? '' }} {{ $invoice->client_city }}</div>@endif

    <hr class="accent-line">

    {{-- Lines --}}
    <table class="lines-table">
        <thead>
            <tr>
                <th style="width:38%">Descripción</th>
                <th class="right" style="width:8%">Cant.</th>
                <th class="right" style="width:14%">P. unit.</th>
                <th class="center" style="width:8%">Dto.</th>
                <th class="center" style="width:8%">IVA</th>
                <th class="right" style="width:12%">Subtotal</th>
                <th class="right" style="width:12%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
            <tr>
                <td>
                    <div class="line-desc">{{ $line->description }}</div>
                    @if($line->unit)<div class="line-unit">{{ $line->unit }}</div>@endif
                </td>
                <td class="right">{{ rtrim(rtrim(number_format($line->quantity, 3, ',', '.'), '0'), ',') }}</td>
                <td class="right">{{ number_format($line->unit_price, 2, ',', '.') }} €</td>
                <td class="center">{{ $line->discount > 0 ? $line->discount . '%' : '—' }}</td>
                <td class="center">{{ $line->vat_rate }}%</td>
                <td class="right">{{ number_format($line->subtotal, 2, ',', '.') }} €</td>
                <td class="right" style="font-weight:600;">{{ number_format($line->total, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="totals-outer">
        <tr>
            <td class="totals-spacer"></td>
            <td class="totals-box">
                <table class="totals-inner">
                    <tr>
                        <td class="label">Base imponible</td>
                        <td class="amount">{{ number_format($invoice->subtotal, 2, ',', '.') }} €</td>
                    </tr>
                    @foreach($invoice->vat_breakdown ?? [] as $vb)
                    <tr>
                        <td class="label">IVA {{ $vb['rate'] }}%</td>
                        <td class="amount">{{ number_format($vb['amount'], 2, ',', '.') }} €</td>
                    </tr>
                    @endforeach
                    @if($invoice->irpf_amount > 0)
                    <tr class="irpf">
                        <td class="label">Ret. IRPF</td>
                        <td class="amount">−{{ number_format($invoice->irpf_amount, 2, ',', '.') }} €</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="total-label">TOTAL</td>
                        <td class="total-amount">{{ number_format($invoice->total, 2, ',', '.') }} €</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Notes & payment --}}
    @if($invoice->notes || $invoice->payment_terms)
    <table class="info-table">
        @if($invoice->notes)
        <tr>
            <td class="info-cell">
                <div class="info-title">Notas</div>
                <div class="info-text">{{ $invoice->notes }}</div>
            </td>
        </tr>
        @endif
        @if($invoice->payment_terms)
        <tr>
            <td class="info-cell" style="padding-top:3mm;">
                <div class="info-title">Condiciones de pago</div>
                <div class="info-text">{{ $invoice->payment_terms }}</div>
            </td>
        </tr>
        @endif
    </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        @if(!empty($company['invoice_footer_text']))
            {{ $company['invoice_footer_text'] }}
        @else
            {{ $company['name'] }}@if(!empty($company['nif'])) · NIF {{ $company['nif'] }}@endif
        @endif
    </div>

</div>
</body>
</html>
