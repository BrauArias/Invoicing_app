<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #333; background: #fff; }
    .page { padding: 20mm 18mm; }

    /* Header */
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8mm; }
    .header-logo { width: 40%; vertical-align: top; }
    .header-logo img { max-width: 120px; max-height: 60px; }
    .header-company-name { font-size: 14pt; font-weight: bold; color: {{ $company['primary_color'] ?? '#1e3a5f' }}; }
    .header-company-sub { font-size: 8pt; color: #666; margin-top: 2px; }
    .header-invoice { width: 60%; vertical-align: top; text-align: right; }
    .invoice-title { font-size: 18pt; font-weight: bold; color: {{ $company['primary_color'] ?? '#1e3a5f' }}; }
    .invoice-number { font-size: 11pt; color: #555; margin-top: 2px; }

    /* Divider */
    .divider { border: none; border-top: 2px solid {{ $company['primary_color'] ?? '#1e3a5f' }}; margin: 5mm 0; }

    /* Parties */
    .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 6mm; }
    .party-cell { width: 50%; vertical-align: top; padding-right: 5mm; }
    .party-label { font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5pt;
                   color: #fff; background: {{ $company['primary_color'] ?? '#1e3a5f' }};
                   padding: 2px 5px; margin-bottom: 4px; display: block; }
    .party-name { font-size: 10pt; font-weight: bold; margin-bottom: 2px; }
    .party-detail { font-size: 8pt; color: #555; line-height: 1.4; }

    /* Dates table */
    .dates-table { width: 100%; border-collapse: collapse; margin-bottom: 6mm;
                   border: 1px solid #ddd; }
    .dates-table td { padding: 3px 6px; font-size: 8.5pt; border-right: 1px solid #eee; }
    .dates-table .label { font-weight: bold; background: #f5f5f5; color: #555; width: 25%; }

    /* Lines table */
    .lines-table { width: 100%; border-collapse: collapse; margin-bottom: 6mm; }
    .lines-table thead tr { background: {{ $company['primary_color'] ?? '#1e3a5f' }}; color: #fff; }
    .lines-table thead th { padding: 4px 6px; font-size: 8pt; font-weight: bold; text-align: left; }
    .lines-table thead th.right { text-align: right; }
    .lines-table thead th.center { text-align: center; }
    .lines-table tbody tr { border-bottom: 1px solid #eee; }
    .lines-table tbody tr:nth-child(even) { background: #f9f9f9; }
    .lines-table tbody td { padding: 4px 6px; font-size: 9pt; vertical-align: top; }
    .lines-table tbody td.right { text-align: right; }
    .lines-table tbody td.center { text-align: center; }
    .line-desc { font-weight: 500; }
    .line-unit { font-size: 7.5pt; color: #888; }

    /* Totals */
    .totals-table { width: 55%; margin-left: 45%; border-collapse: collapse; margin-bottom: 6mm; }
    .totals-table td { padding: 3px 6px; font-size: 9pt; }
    .totals-table .label { color: #555; }
    .totals-table .amount { text-align: right; font-weight: 500; }
    .totals-table .total-row td { font-size: 11pt; font-weight: bold;
                                   background: {{ $company['primary_color'] ?? '#1e3a5f' }};
                                   color: #fff; padding: 5px 8px; }
    .totals-table .irpf td { color: #c0392b; }

    /* Payment */
    .payment-box { background: #f5f5f5; border: 1px solid #ddd; padding: 4mm; margin-bottom: 6mm; }
    .payment-box .title { font-size: 8pt; font-weight: bold; text-transform: uppercase; color: #555; margin-bottom: 3px; }
    .payment-box .detail { font-size: 8.5pt; color: #444; }

    /* Notes */
    .notes-box { border-left: 3px solid {{ $company['accent_color'] ?? '#d4a017' }};
                 padding: 3mm 4mm; background: #fffdf0; margin-bottom: 6mm; }
    .notes-box .title { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; color: #888; margin-bottom: 2px; }
    .notes-box .text { font-size: 8.5pt; color: #555; line-height: 1.5; }

    /* Footer */
    .footer { border-top: 1px solid #ddd; padding-top: 3mm; font-size: 7.5pt; color: #888; text-align: center; margin-top: 4mm; }

    .accent { color: {{ $company['accent_color'] ?? '#d4a017' }}; }
    .bold { font-weight: bold; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(!empty($company['logo_base64']))
                    <img src="data:image/png;base64,{{ $company['logo_base64'] }}" alt="{{ $company['name'] }}">
                @else
                    <div class="header-company-name">{{ $company['name'] }}</div>
                @endif
                @if(!empty($company['logo_base64']))
                    <div class="header-company-name" style="margin-top:4px;">{{ $company['name'] }}</div>
                @endif
                @if(!empty($company['trade_name']) && $company['trade_name'] !== $company['name'])
                    <div class="header-company-sub">{{ $company['trade_name'] }}</div>
                @endif
                @if(!empty($company['nif']))
                    <div class="header-company-sub">NIF: {{ $company['nif'] }}</div>
                @endif
                @if(!empty($company['address']))
                    <div class="header-company-sub">{{ $company['address'] }}</div>
                @endif
                @if(!empty($company['city']))
                    <div class="header-company-sub">{{ $company['postal_code'] ?? '' }} {{ $company['city'] }}{{ !empty($company['province']) ? ', ' . $company['province'] : '' }}</div>
                @endif
            </td>
            <td class="header-invoice">
                <div class="invoice-title">
                    @switch($invoice->type)
                        @case('proforma') FACTURA PROFORMA @break
                        @case('quote') PRESUPUESTO @break
                        @case('credit_note') FACTURA RECTIFICATIVA @break
                        @default FACTURA @break
                    @endswitch
                </div>
                @if($invoice->full_number)
                    <div class="invoice-number">{{ $invoice->full_number }}</div>
                @else
                    <div class="invoice-number" style="color:#aaa;font-style:italic;">BORRADOR</div>
                @endif
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- Dates --}}
    <table class="dates-table">
        <tr>
            <td class="label">Fecha emisión</td>
            <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
            @if($invoice->due_date)
            <td class="label">Vencimiento</td>
            <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
            @endif
            @if($invoice->service_date)
            <td class="label">Fecha servicio</td>
            <td>{{ \Carbon\Carbon::parse($invoice->service_date)->format('d/m/Y') }}</td>
            @endif
            @if($invoice->paid_at)
            <td class="label" style="color:#27ae60;">Cobrada</td>
            <td style="color:#27ae60;">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d/m/Y') }}</td>
            @endif
        </tr>
    </table>

    {{-- Parties --}}
    <table class="parties-table">
        <tr>
            <td class="party-cell">
                <span class="party-label">Emisor</span>
                <div class="party-name">{{ $company['name'] }}</div>
                @if(!empty($company['nif']))<div class="party-detail">NIF: {{ $company['nif'] }}</div>@endif
                @if(!empty($company['address']))<div class="party-detail">{{ $company['address'] }}</div>@endif
                @if(!empty($company['city']))<div class="party-detail">{{ $company['postal_code'] ?? '' }} {{ $company['city'] }}{{ !empty($company['province']) ? ', ' . $company['province'] : '' }}</div>@endif
                @if(!empty($company['phone']))<div class="party-detail">Tel: {{ $company['phone'] }}</div>@endif
                @if(!empty($company['email']))<div class="party-detail">{{ $company['email'] }}</div>@endif
            </td>
            <td class="party-cell">
                <span class="party-label">Facturado a</span>
                <div class="party-name">{{ $invoice->client_name }}</div>
                @if($invoice->client_nif)<div class="party-detail">NIF: {{ $invoice->client_nif }}</div>@endif
                @if($invoice->client_address)<div class="party-detail">{{ $invoice->client_address }}</div>@endif
                @if($invoice->client_city)<div class="party-detail">{{ $invoice->client_postal_code ?? '' }} {{ $invoice->client_city }}</div>@endif
            </td>
        </tr>
    </table>

    {{-- Lines --}}
    <table class="lines-table">
        <thead>
            <tr>
                <th style="width:40%">Descripción</th>
                <th class="right" style="width:8%">Cant.</th>
                <th class="right" style="width:13%">Precio unit.</th>
                <th class="center" style="width:8%">Dto.</th>
                <th class="center" style="width:8%">IVA</th>
                <th class="right" style="width:13%">Subtotal</th>
                <th class="right" style="width:10%">Total</th>
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
                <td class="right bold">{{ number_format($line->total, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="totals-table">
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
            <td class="label">Ret. IRPF ({{ $invoice->irpf_rate ?? '' }}%)</td>
            <td class="amount">−{{ number_format($invoice->irpf_amount, 2, ',', '.') }} €</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>TOTAL A PAGAR</td>
            <td style="text-align:right;">{{ number_format($invoice->total, 2, ',', '.') }} €</td>
        </tr>
    </table>

    {{-- Payment info --}}
    @if($invoice->payment_method || $invoice->payment_terms || (!empty($company['show_bank_details']) && !empty($company['iban'])))
    <div class="payment-box">
        <div class="title">Datos de pago</div>
        @if($invoice->payment_method)<div class="detail">Forma de pago: {{ ucfirst($invoice->payment_method) }}</div>@endif
        @if($invoice->payment_terms)<div class="detail">{{ $invoice->payment_terms }}</div>@endif
        @if(!empty($company['show_bank_details']) && !empty($company['iban']))
            <div class="detail">IBAN: {{ $company['iban'] }}@if(!empty($company['swift'])) · SWIFT: {{ $company['swift'] }}@endif</div>
        @endif
    </div>
    @endif

    {{-- Notes --}}
    @if($invoice->notes)
    <div class="notes-box">
        <div class="title">Notas</div>
        <div class="text">{{ $invoice->notes }}</div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        @if(!empty($company['invoice_footer_text']))
            {{ $company['invoice_footer_text'] }}
        @else
            {{ $company['name'] }} · NIF {{ $company['nif'] ?? '' }}
            @if(!empty($company['address'])) · {{ $company['address'] }}, {{ $company['city'] ?? '' }}@endif
        @endif
    </div>

</div>
</body>
</html>
