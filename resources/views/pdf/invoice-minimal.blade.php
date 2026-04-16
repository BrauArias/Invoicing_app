<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #111; background: #fff; }
    .page { padding: 25mm 20mm; }

    /* Header */
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12mm; }
    .header-logo { width: 50%; vertical-align: top; }
    .header-logo img { max-width: 140px; max-height: 70px; }
    .header-company-name { font-size: 16pt; font-weight: 300; letter-spacing: 1px; margin-bottom: 4px; }
    .header-company-sub { font-size: 8pt; color: #666; line-height: 1.4; }
    
    .header-invoice { width: 50%; vertical-align: top; text-align: right; }
    .invoice-title { font-size: 22pt; font-weight: 200; letter-spacing: 2px; text-transform: uppercase; color: #000; margin-bottom: 5px; }
    .invoice-number { font-size: 10pt; font-weight: bold; }

    /* Parties & Dates */
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10mm; }
    .info-table td { vertical-align: top; }
    .info-col-right { text-align: right; }

    .party-label { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 3px; }
    .party-name { font-size: 10pt; font-weight: bold; margin-bottom: 2px; }
    .party-detail { font-size: 8.5pt; color: #444; line-height: 1.4; }

    .dates-box { margin-top: 15px; }
    .dates-table { border-collapse: collapse; display: inline-table; float: right; text-align: left;}
    .dates-table td { padding: 2px 0 2px 15px; font-size: 8.5pt; }
    .dates-table .label { color: #888; text-transform: uppercase; font-size: 7pt; letter-spacing: 0.5px; }
    .dates-table .val { font-weight: bold; text-align: right;}

    /* Lines table */
    .lines-table { width: 100%; border-collapse: collapse; margin-bottom: 8mm; border-bottom: 1px solid #000; }
    .lines-table th { padding: 8px 4px; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #000; border-bottom: 1px solid #000; text-align: left; }
    .lines-table th.right { text-align: right; }
    .lines-table th.center { text-align: center; }
    
    .lines-table td { padding: 8px 4px; font-size: 9pt; border-bottom: 1px solid #eee; vertical-align: top; }
    .lines-table td.right { text-align: right; }
    .lines-table td.center { text-align: center; }
    .line-desc { font-weight: bold; color: #222; }
    .line-unit { font-size: 7.5pt; color: #888; margin-top: 2px; }

    /* Totals */
    .totals-table { width: 45%; margin-left: 55%; border-collapse: collapse; margin-bottom: 10mm; }
    .totals-table td { padding: 5px 4px; font-size: 9pt; border-bottom: 1px solid #eee; }
    .totals-table .label { color: #666; }
    .totals-table .amount { text-align: right; font-weight: bold; }
    .totals-table .total-row td { font-size: 12pt; border-bottom: none; border-top: 2px solid #000; padding-top: 8px; }
    .totals-table .irpf td { color: #555; }

    /* Sections */
    .section-title { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 1px; color: #888; border-bottom: 1px solid #eee; padding-bottom: 3px; margin-bottom: 5px; }
    
    .payment-box { margin-bottom: 8mm; }
    .payment-detail { font-size: 8.5pt; line-height: 1.5; color: #333; }

    .notes-box { margin-bottom: 8mm; }
    .notes-text { font-size: 8.5pt; line-height: 1.5; color: #444; }

    /* Footer */
    .footer { font-size: 7.5pt; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 5mm; margin-top: 10mm; }
    .footer span { padding: 0 5px; border-right: 1px solid #ddd; }
    .footer span:last-child { border-right: none; }
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
                <div class="header-company-sub">
                    @if(!empty($company['trade_name']) && $company['trade_name'] !== $company['name'])
                        {{ $company['trade_name'] }}<br>
                    @endif
                    @if(!empty($company['nif'])) CIF/NIF: {{ $company['nif'] }}<br>@endif
                    @if(!empty($company['address']))
                        {{ $company['address'] }}<br>
                        {{ $company['postal_code'] ?? '' }} {{ $company['city'] }}{{ !empty($company['province']) ? ', ' . $company['province'] : '' }}
                    @endif
                </div>
            </td>
            <td class="header-invoice">
                <div class="invoice-title" style="color: {{ $company['primary_color'] ?? '#000' }};">
                    @switch($invoice->type)
                        @case('proforma') PROFORMA @break
                        @case('quote') PRESUPUESTO @break
                        @case('credit_note') RECTIFICATIVA @break
                        @default FACTURA @break
                    @endswitch
                </div>
                <div class="invoice-number">{{ $invoice->full_number ?: 'BORRADOR' }}</div>
                
                <div class="dates-box">
                    <table class="dates-table">
                        <tr>
                            <td class="label">Fecha</td>
                            <td class="val">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</td>
                        </tr>
                        @if($invoice->service_date && $invoice->service_date->format('Y-m-d') !== $invoice->issue_date->format('Y-m-d'))
                        <tr>
                            <td class="label">F. Operación</td>
                            <td class="val">{{ \Carbon\Carbon::parse($invoice->service_date)->format('d.m.Y') }}</td>
                        </tr>
                        @endif
                        @if($invoice->due_date)
                        <tr>
                            <td class="label">Vence</td>
                            <td class="val">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Facturado a --}}
    <table class="info-table">
        <tr>
            <td style="width: 50%">
                <div class="party-label">Facturado a</div>
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
                <th style="width:45%">Concepto</th>
                <th class="right" style="width:10%">Cant.</th>
                <th class="right" style="width:12%">Precio</th>
                <th class="center" style="width:8%">Dto.</th>
                <th class="center" style="width:8%">IVA</th>
                <th class="right" style="width:17%">Importe</th>
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
                <td class="right">{{ number_format($line->total, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="totals-table">
        <tr>
            <td class="label">Subtotal</td>
            <td class="amount">{{ number_format($invoice->subtotal, 2, ',', '.') }} €</td>
        </tr>
        @foreach($invoice->vat_breakdown ?? [] as $vb)
        <tr>
            <td class="label">IVA ({{ $vb['rate'] }}%)</td>
            <td class="amount">{{ number_format($vb['amount'], 2, ',', '.') }} €</td>
        </tr>
        @endforeach
        @if($invoice->irpf_amount > 0)
        <tr class="irpf">
            <td class="label">IRPF ({{ $invoice->irpf_rate ?? '' }}%)</td>
            <td class="amount">−{{ number_format($invoice->irpf_amount, 2, ',', '.') }} €</td>
        </tr>
        @endif
        <tr class="total-row">
            <td class="label" style="color:#000; font-weight:bold;">TOTAL</td>
            <td class="amount" style="color: {{ $company['accent_color'] ?? '#000' }};">{{ number_format($invoice->total, 2, ',', '.') }} €</td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    {{-- Payment info --}}
    @if($invoice->payment_method || $invoice->payment_terms || (!empty($company['show_bank_details']) && !empty($company['iban'])))
    <div class="payment-box">
        <div class="section-title">Información de pago</div>
        @if($invoice->payment_method)<div class="payment-detail">Método: {{ ucfirst($invoice->payment_method) }}</div>@endif
        @if($invoice->payment_terms)<div class="payment-detail">Términos: {{ $invoice->payment_terms }}</div>@endif
        @if(!empty($company['show_bank_details']) && !empty($company['iban']))
            <div class="payment-detail">IBAN: {{ $company['iban'] }} @if(!empty($company['swift'])) (SWIFT: {{ $company['swift'] }})@endif</div>
        @endif
    </div>
    @endif

    {{-- Notes --}}
    @if($invoice->notes)
    <div class="notes-box">
        <div class="section-title">Notas</div>
        <div class="notes-text">{{ $invoice->notes }}</div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        @if(!empty($company['invoice_footer_text']))
            {{ $company['invoice_footer_text'] }}
        @else
            <span>{{ $company['name'] }}</span>
            <span>{{ $company['nif'] ?? '' }}</span>
            @if(!empty($company['email']))<span>{{ $company['email'] }}</span>@endif
            @if(!empty($company['website']))<span>{{ $company['website'] }}</span>@endif
        @endif
    </div>

</div>
</body>
</html>
