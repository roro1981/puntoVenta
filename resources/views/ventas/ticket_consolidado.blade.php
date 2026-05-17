<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consolidado de Cajas</title>
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
            max-width: 60mm;
            max-height: 25mm;
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

        .subtitle {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin-bottom: 6px;
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

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-weight: bold;
        }

        .cierres-list {
            margin-top: 4px;
        }

        .cierre-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 4px;
            padding: 3px 0;
            border-bottom: 1px dotted #000;
            font-weight: bold;
            color: #000;
        }

        .cierre-item:last-child {
            border-bottom: none;
        }

        .cierre-label {
            width: 68%;
            overflow: hidden;
            word-break: break-word;
            line-height: 1.25;
        }

        .cierre-monto {
            width: 32%;
            text-align: right;
            white-space: nowrap;
            font-size: 11px;
        }

        .totals-section {
            margin-top: 8px;
            border-top: 2px solid #000;
            padding-top: 6px;
            padding: 8px 2px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin: 3px 0;
            font-size: 11px;
        }

        .total-esperado {
            font-size: 12px;
            font-weight: bold;
            border: 1px dashed #000;
            padding: 5px;
            margin: 6px auto;
            box-sizing: border-box;
            width: 98%;
        }

        .total-esperado .total-row {
            margin: 0;
            border: none;
        }

        .total-declarado {
            font-size: 12px;
            font-weight: bold;
            border: 1px dashed #000;
            padding: 5px;
            margin: 6px auto;
            box-sizing: border-box;
            width: 98%;
        }

        .total-declarado .total-row {
            margin: 0;
            border: none;
        }

        .diferencia {
            font-size: 13px;
            font-weight: bold;
            padding: 8px 4px;
            margin: 8px auto;
            text-align: center;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            width: 98%;
        }

        .diferencia.positiva {
            border-top-style: double;
            border-bottom-style: double;
        }

        .diferencia.negativa {
            border-top-style: double;
            border-bottom-style: double;
        }

        .diferencia.exacta {
            border-top-style: solid;
            border-bottom-style: solid;
        }

        .footer {
            text-align: center;
            margin: 12px auto 0;
            padding-top: 8px;
            font-size: 11px;
            width: 98%;
            font-weight: bold;
        }

        .footer p {
            margin: 3px 0;
        }

        .warning-text {
            margin: 10px auto 0;
            padding: 5px;
            border-top: 1px dashed #000;
            padding-top: 5px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            width: 98%;
        }
    </style>
</head>
<body>
    <!-- Encabezado con logo y datos empresa -->
    <div class="header">
        @php
            $logoSrc = null;
            if (isset($corporateData['logo_enterprise']) && $corporateData['logo_enterprise'] && $corporateData['logo_enterprise'] !== '/img/fotos_prod/sin_imagen.jpg') {
                $logoPath = realpath(public_path(ltrim($corporateData['logo_enterprise'], '/')));
                if ($logoPath && file_exists($logoPath)) {
                    $mime = mime_content_type($logoPath);
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                }
            }
        @endphp
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="Logo" class="logo">
        @endif

        @if(isset($corporateData['fantasy_name_enterprise']) && $corporateData['fantasy_name_enterprise'])
        <div style="font-weight:bold;font-size:13px;margin:4px 0 2px;">{{ $corporateData['fantasy_name_enterprise'] }}</div>
        @else
        <div style="font-weight:bold;font-size:13px;margin:4px 0 2px;">{{ $corporateData['name_enterprise'] ?? '' }}</div>
        @endif
    </div>

    <!-- Título -->
    <div class="title-box">
        CONSOLIDADO DE CAJAS
    </div>
    <div class="subtitle">
        {{ $cajas->first()->fecha_apertura->format('d/m/Y H:i') }} — {{ $cajas->last()->fecha_cierre->format('d/m/Y H:i') }}
    </div>

    <div class="separator"></div>

    <!-- Detalle por caja -->
    <div class="section-title">CIERRES INCLUIDOS</div>

    <div class="cierres-list">
        @foreach($cajas as $caja)
        <div class="cierre-item">
            <span class="cierre-label">CIERRE {{ sprintf('%04d', $caja->id) }}</span>
            <span class="cierre-monto">${{ number_format($caja->monto_ventas, 0, ',', '.') }} - {{ Str::limit($caja->usuario->name ?? 'N/A', 12) }}</span>
        </div>
        @endforeach
    </div>

    <div class="separator"></div>

    <!-- Resumen de Ventas -->
    <div class="section-title">RESUMEN CONSOLIDADO</div>

    <div class="info-row">
        <span>TURNOS INCLUIDOS:</span>
        <span>{{ $cajas->count() }}</span>
    </div>
    <div class="info-row">
        <span>CANTIDAD DE VENTAS:</span>
        <span>{{ $cantidadVentas }}</span>
    </div>
    <div class="info-row">
        <span>TOTAL VENTAS:</span>
        <span>${{ number_format($totalVentas, 0, ',', '.') }}</span>
    </div>

    <div class="separator"></div>

    <!-- Desglose por Forma de Pago -->
    <div class="section-title">DESGLOSE POR FORMA DE PAGO</div>

    @if($desglose['efectivo'] > 0)
    <div class="info-row">
        <span>EFECTIVO</span>
        <span>${{ number_format($desglose['efectivo'], 0, ',', '.') }}</span>
    </div>
    @endif

    @if($desglose['tarjeta_debito'] > 0)
    <div class="info-row">
        <span>TARJETA DEBITO</span>
        <span>${{ number_format($desglose['tarjeta_debito'], 0, ',', '.') }}</span>
    </div>
    @endif

    @if($desglose['tarjeta_credito'] > 0)
    <div class="info-row">
        <span>TARJETA CREDITO</span>
        <span>${{ number_format($desglose['tarjeta_credito'], 0, ',', '.') }}</span>
    </div>
    @endif

    @if($desglose['transferencia'] > 0)
    <div class="info-row">
        <span>TRANSFERENCIA</span>
        <span>${{ number_format($desglose['transferencia'], 0, ',', '.') }}</span>
    </div>
    @endif

    @if($desglose['cheque'] > 0)
    <div class="info-row">
        <span>CHEQUE</span>
        <span>${{ number_format($desglose['cheque'], 0, ',', '.') }}</span>
    </div>
    @endif

    <div class="separator"></div>


    <!-- Totales Consolidados -->
    <div class="totals-section">
        <div class="total-row">
            <span>MONTO INICIAL TOTAL:</span>
            <span>${{ number_format($montoInicialTotal, 0, ',', '.') }}</span>
        </div>

        <div class="total-row">
            <span>TOTAL VENTAS:</span>
            <span>${{ number_format($totalVentas, 0, ',', '.') }}</span>
        </div>

        @if(isset($totalRetiros) && $totalRetiros > 0)
        <div class="total-row">
            <span>TOTAL RETIROS:</span>
            <span>-${{ number_format($totalRetiros, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="total-esperado">
            <div class="total-row">
                <span>MONTO ESPERADO:</span>
                <span>${{ number_format($montoInicialTotal + $totalVentas - ($totalRetiros ?? 0), 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="total-declarado">
            <div class="total-row">
                <span>MONTO DECLARADO:</span>
                <span>${{ number_format($montoDeclaradoTotal, 0, ',', '.') }}</span>
            </div>
        </div>

        @php
            $claseDiv = 'exacta';
            $textoDif = 'CUADRE EXACTO';
            if ($diferenciaTotal > 0) {
                $claseDiv = 'positiva';
                $textoDif = 'SOBRANTE: $' . number_format($diferenciaTotal, 0, ',', '.');
            } elseif ($diferenciaTotal < 0) {
                $claseDiv = 'negativa';
                $textoDif = 'FALTANTE: $' . number_format(abs($diferenciaTotal), 0, ',', '.');
            }
        @endphp

        <div class="diferencia {{ $claseDiv }}">
            {{ $textoDif }}
        </div>
    </div>

    <!-- Advertencia -->
    <div class="warning-text">
        ESTE DOCUMENTO NO ES VÁLIDO COMO COMPROBANTE TRIBUTARIO
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p>Impreso: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
