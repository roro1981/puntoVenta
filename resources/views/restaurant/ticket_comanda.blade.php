<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ !empty($esTicketPago) ? 'Ticket Pago Comanda' : 'Comanda' }} {{ $comanda->numero_comanda ?? ('#' . $comanda->id) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            width: 72mm;
            padding: 1mm 2mm 2mm 2mm;
            max-width: 72mm;
        }

        .header {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo {
            max-width: 68mm;
            max-height: 30mm;
            margin: 0 auto 8px;
            display: block;
            height: auto;
        }

        .title-box {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 10px 0;
            padding: 6px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            letter-spacing: 1px;
        }

        .info-section {
            margin: 8px 0;
            font-size: 10px;
            border: 1px solid #ddd;
            padding: 4px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-weight: bold;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin: 8px 0 4px 0;
            text-align: center;
            text-decoration: underline;
        }

        table {
            width: 100%;
            margin: 5px 0;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
        }

        table th,
        table td {
            padding: 2px 0;
            vertical-align: top;
            overflow: hidden;
            word-wrap: break-word;
        }

        table th {
            font-weight: bold;
            border-bottom: 1px dashed #000;
        }

        .col-cant {
            width: 15%;
            text-align: left;
        }

        .col-desc {
            width: 55%;
            text-align: left;
            padding-right: 4px;
        }

        .col-total {
            width: 30%;
            text-align: right;
        }

        .totals-section {
            margin-top: 8px;
            border-top: 2px solid #000;
            padding: 8px 2px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin: 3px 0;
            font-size: 11px;
        }

        .footer {
            text-align: center;
            margin: 12px auto 0;
            padding-top: 8px;
            border-top: 2px dashed #000;
            font-size: 10px;
            width: 98%;
            font-weight: bold;
        }

        .footer p {
            margin: 3px 0;
        }

        .payment-section {
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 6px;
            font-size: 10px;
        }

        .payment-title {
            font-weight: bold;
            margin-bottom: 3px;
            text-align: center;
            text-decoration: underline;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(isset($corporateData['logo_enterprise']) && $corporateData['logo_enterprise'] && $corporateData['logo_enterprise'] != '/img/fotos_prod/sin_imagen.jpg' && file_exists(public_path($corporateData['logo_enterprise'])))
            <img src="{{ public_path($corporateData['logo_enterprise']) }}" alt="Logo" class="logo">
        @endif
    </div>

    <div class="title-box">
        {{ !empty($esTicketPago) ? 'TICKET PAGO COMANDA' : 'COMANDA' }} {{ $comanda->numero_comanda ?? ('#' . str_pad($comanda->id, 4, '0', STR_PAD_LEFT)) }}
    </div>

    <div class="info-section">
        <div class="info-row">
            <span>MESA:</span>
            <span>{{ optional($comanda->mesa)->nombre ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span>GARZÓN:</span>
            <span>{{ optional($comanda->garzon)->nombre_completo ?? optional($comanda->user)->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span>COMENSALES:</span>
            <span>{{ (int)($comanda->comensales ?? 0) }}</span>
        </div>
        <div class="info-row">
            <span>ESTADO:</span>
            <span>{{ !empty($esTicketPago) ? 'PAGADA' : ($comanda->estado ?? 'N/A') }}</span>
        </div>
        <div class="info-row">
            <span>APERTURA:</span>
            <span>{{ $comanda->fecha_apertura ? $comanda->fecha_apertura->format('d/m/Y H:i:s') : 'N/A' }}</span>
        </div>
        @if(!empty($esTicketPago) && isset($venta) && $venta->fecha_venta)
            <div class="info-row">
                <span>PAGO:</span>
                <span>{{ $venta->fecha_venta->format('d/m/Y H:i:s') }}</span>
            </div>
        @endif
    </div>

    <div class="separator"></div>
    <div class="section-title">DETALLE</div>

    <table>
        <thead>
            <tr>
                <th class="col-cant">CANT</th>
                <th class="col-desc">PRODUCTO</th>
                <th class="col-total">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comanda->detalles as $detalle)
                @php
                    $esReceta = ($detalle->tipo_item ?? 'PRODUCTO') === 'RECETA';
                    $nombreDetalle = $esReceta
                        ? (optional($detalle->receta)->nombre ?? optional($detalle->producto)->descripcion ?? 'Receta')
                        : (optional($detalle->producto)->descripcion ?? optional($detalle->producto)->nombre_producto ?? optional($detalle->producto)->nombre ?? 'Producto');
                @endphp
                <tr>
                    <td class="col-cant">{{ (int)$detalle->cantidad }}</td>
                    <td class="col-desc">{{ $nombreDetalle }}</td>
                    <td class="col-total">${{ number_format((float)$detalle->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if(!empty($detalle->observaciones))
                    <tr>
                        <td></td>
                        <td colspan="2" style="font-size: 9px;">Obs: {{ $detalle->observaciones }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="total-row">
            <span>SUBTOTAL:</span>
            <span>${{ number_format((float)$comanda->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>PROPINA ({{ rtrim(rtrim(number_format((float)($porcentajePropinaGlobal ?? 10), 2, '.', ''), '0'), '.') }}%):</span>
            <span>${{ number_format((float)$comanda->propina, 0, ',', '.') }}</span>
        </div>
        <div class="total-row" style="font-size: 12px; border-top: 1px solid #000; padding-top: 3px; margin-top: 4px;">
            <span>TOTAL:</span>
            <span>${{ number_format((float)$comanda->total, 0, ',', '.') }}</span>
        </div>
    </div>

    @if(!empty($esTicketPago) && isset($venta))
        <div class="payment-section">
            <div class="payment-title">DETALLE DE PAGO</div>
            @if($venta->formasPago && $venta->formasPago->count() > 0)
                @foreach($venta->formasPago as $fp)
                    <div class="payment-row">
                        <span>{{ str_replace('_', ' ', $fp->forma_pago) }}</span>
                        <span>${{ number_format((float)$fp->monto, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            @else
                <div class="payment-row">
                    <span>{{ str_replace('_', ' ', $venta->forma_pago ?? 'N/A') }}</span>
                    <span>${{ number_format((float)$comanda->total, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="payment-row">
                <span>TICKET:</span>
                <span>N° {{ str_pad($venta->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>{{ $corporateData['name_enterprise'] ?? 'RESTAURANT' }}</p>
        <p>{{ $corporateData['address_enterprise'] ?? '' }}</p>
        <p>{{ $corporateData['phone_enterprise'] ?? '' }}</p>
    </div>
</body>
</html>