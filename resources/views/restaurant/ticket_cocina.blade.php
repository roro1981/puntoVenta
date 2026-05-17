<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Cocina - {{ $comanda->numero_comanda ?? ('#' . $comanda->id) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: #000 !important;
        }

        @page {
            size: 80mm 210mm;
            margin: 0;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: bold;
            line-height: 1.4;
            color: #000;
            width: 72mm;
            padding: 1mm 2mm 2mm 2mm;
            max-width: 72mm;
        }

        .title-box {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin: 6px 0;
            padding: 6px 0;
            border-top: 3px solid #000;
            border-bottom: 3px solid #000;
            letter-spacing: 2px;
        }

        .comanda-num {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .info-section {
            margin: 6px 0;
            font-size: 11px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .info-label {
            font-weight: bold;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .separator-solid {
            border-top: 2px solid #000;
            margin: 8px 0;
        }

        .section-title {
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            text-decoration: underline;
            margin: 6px 0 4px 0;
        }

        .item {
            margin: 6px 0;
            padding-bottom: 4px;
            border-bottom: 1px dashed #ccc;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-main {
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .item-cant {
            font-size: 20px;
            font-weight: bold;
            min-width: 28px;
            text-align: right;
            line-height: 1.1;
        }

        .item-x {
            font-size: 14px;
            font-weight: bold;
            margin: 0 2px;
        }

        .item-desc {
            font-size: 13px;
            font-weight: bold;
            flex: 1;
            word-wrap: break-word;
        }

        .item-obs {
            font-size: 11px;
            font-style: italic;
            margin-left: 38px;
            color: #000;
            font-weight: bold;
        }

        .obs-general {
            padding: 5px 6px;
            margin: 4px 0;
            font-size: 12px;
            font-weight: bold;
            width: 100%;
        }

        .obs-general-title {
            text-align: left;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .total-items {
            text-align: center;
            font-size: 11px;
            margin-top: 6px;
            font-weight: bold;
            color: #000;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 6px;
            border-top: 2px dashed #000;
            font-size: 11px;
            font-weight: bold;
            color: #000;
        }

        .print-time {
            text-align: center;
            font-size: 10px;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    @php
        $detallesLista = isset($detallesTicket) ? $detallesTicket : $comanda->detalles;
        $titulo = strtoupper((string)($tituloTicket ?? 'COCINA'));
    @endphp

    <div class="title-box">*** {{ $titulo }} ***</div>

    <div class="comanda-num">
        {{ $comanda->numero_comanda ?? ('#' . str_pad($comanda->id, 4, '0', STR_PAD_LEFT)) }}
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">MESA:</span>
            <span>{{ optional($comanda->mesa)->nombre ?? 'N/A' }}</span>
        </div>
        @if(optional($comanda->garzon)->nombre_completo)
        <div class="info-row">
            <span class="info-label">GARZÓN:</span>
            <span>{{ $comanda->garzon->nombre_completo }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">COMENSALES:</span>
            <span>{{ (int)($comanda->comensales ?? 0) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">HORA:</span>
            <span>{{ $comanda->fecha_apertura ? $comanda->fecha_apertura->format('H:i') : now()->format('H:i') }}</span>
        </div>
    </div>

    <div class="separator-solid"></div>
    <div class="section-title">PEDIDO</div>
    <div class="separator"></div>

    @if(!empty($comanda->observaciones))
        <div class="obs-general">
            <div>{{ $comanda->observaciones }}</div>
        </div>
        <div class="separator"></div>
    @endif

    @forelse($detallesLista as $detalle)
        @php
            $esReceta = ($detalle->tipo_item ?? 'PRODUCTO') === 'RECETA';
            $descripcion = $esReceta
                ? (optional($detalle->receta)->nombre ?? optional($detalle->producto)->descripcion ?? 'Receta')
                : (optional($detalle->producto)->descripcion ?? optional($detalle->producto)->nom_prod ?? 'Producto');
            $cantidad = (int) $detalle->cantidad;
        @endphp
        <div class="item">
            <div class="item-main">
                <span class="item-cant">{{ $cantidad }}</span>
                <span class="item-x">x</span>
                <span class="item-desc">{{ mb_strtoupper($descripcion, 'UTF-8') }}</span>
            </div>
            @if(!empty($detalle->observaciones))
                <div class="item-obs">↳ {{ $detalle->observaciones }}</div>
            @endif
        </div>
    @empty
        <div class="item">
            <div class="item-desc">SIN PRODUCTOS PARA ESTE SECTOR</div>
        </div>
    @endforelse

    <div class="separator"></div>
    <div class="total-items">
        Total ítems: {{ collect($detallesLista)->sum('cantidad') }}
    </div>

    <div class="footer">
        <p>Impreso: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
