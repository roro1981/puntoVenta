<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cierre de Caja</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            font-weight: bold;
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
        
        .info-section {
            margin: 8px 0;
            font-size: 11px;
            background: #f5f5f5;
            padding: 6px;
            border-radius: 3px;
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

        .retiros-section {
            margin: 6px 0;
        }

        .retiro-item {
            margin-bottom: 8px;
            padding-bottom: 6px;
            padding-left: 1mm;
            padding-right: 1mm;
            border-bottom: 1px dotted #ccc;
        }

        .retiro-item:last-child {
            border-bottom: none;
        }

        .retiro-motivo {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
            color: #000;
        }

        .retiro-line {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            color: #000;
        }

        .retiro-line td {
            padding: 0 1mm;
            vertical-align: middle;
        }

        .retiro-line .left {
            width: 64%;
            text-align: left;
            word-wrap: break-word;
            word-break: break-word;
        }

        .retiro-line .right {
            width: 36%;
            text-align: right;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 800;
        }
        
        table {
            width: 100%;
            margin: 5px 0;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
        }
        
        table td, table th {
            padding: 2px 0;
            vertical-align: top;
            overflow: hidden;
            word-wrap: break-word;
            font-weight: bold;
        }
        
        table td:first-child {
            text-align: left;
            width: 50%;
            padding-right: 4px;
        }
        
        table td:last-child {
            text-align: right;
            width: 50%;
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
        
        .total-label {
            font-weight: bold;
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
            margin: 8px auto 3px;
            text-align: center;
            border-left: none;
            border-right: none;
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
        
        .observaciones {
            margin: 2px auto 6px;
            padding: 6px 4px;
            font-size: 11px;
            word-wrap: break-word;
            width: 98%;
            font-weight: bold;
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
        
        .firma-section {
            margin: 12px auto 0;
            text-align: center;
            font-size: 10px;
            width: 98%;
            font-weight: bold;
        }
        
        .firma-line {
            border-top: 1px solid #000;
            width: 60%;
            margin: 20px auto 5px;
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
        CIERRE DE CAJA N° {{ sprintf('%04d', $caja->id) }}
    </div>

    <!-- Información de Caja -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">CAJERO:</span>
            <span>{{ $caja->usuario->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">APERTURA:</span>
            <span>{{ $caja->fecha_apertura ? $caja->fecha_apertura->format('d/m/Y H:i:s') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">CIERRE:</span>
            <span>{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i:s') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">DURACION:</span>
            <span>{{ ($caja->fecha_apertura && $caja->fecha_cierre) ? $caja->fecha_apertura->diffInHours($caja->fecha_cierre) . ' hrs ' . ($caja->fecha_apertura->diffInMinutes($caja->fecha_cierre) % 60) . ' min' : 'N/A' }}</span>
        </div>
    </div>

    <div class="separator"></div>

    <!-- Resumen de Ventas -->
    <div class="section-title">RESUMEN DE VENTAS</div>
    
    <div class="info-row">
        <span>CANTIDAD DE VENTAS:</span>
        <span class="info-label">{{ $cantidadVentas }}</span>
    </div>
    <div class="info-row">
        <span>TOTAL VENTAS:</span>
        <span class="info-label">${{ number_format($totalVentas, 0, ',', '.') }}</span>
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

    <!-- Totales de Cierre -->
    <div class="totals-section">
        <div class="total-row">
            <span class="total-label">MONTO INICIAL:</span>
            <span>${{ number_format($caja->monto_inicial, 0, ',', '.') }}</span>
        </div>

        <div class="total-row">
            <span class="total-label">TOTAL VENTAS:</span>
            <span>${{ number_format($totalVentas, 0, ',', '.') }}</span>
        </div>

        @if(isset($totalRetiros) && $totalRetiros > 0)
        <div class="total-row">
            <span class="total-label">TOTAL RETIROS:</span>
            <span>-${{ number_format($totalRetiros, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="total-esperado">
            <div class="total-row">
                <span class="total-label">MONTO ESPERADO:</span>
                <span class="total-label">${{ number_format($caja->monto_inicial + $totalVentas - ($totalRetiros ?? 0), 0, ',', '.') }}</span>
            </div>
        </div>
        
        <div class="total-declarado">
            <div class="total-row">
                <span class="total-label">MONTO DECLARADO:</span>
                <span class="total-label">${{ number_format($caja->monto_final_declarado, 0, ',', '.') }}</span>
            </div>
        </div>
        
        @php
            $diferencia = $caja->diferencia;
            $claseDiv = 'exacta';
            $textoDif = 'CUADRE EXACTO';
            
            if ($diferencia > 0) {
                $claseDiv = 'positiva';
                $textoDif = 'SOBRANTE: $' . number_format($diferencia, 0, ',', '.');
            } elseif ($diferencia < 0) {
                $claseDiv = 'negativa';
                $textoDif = 'FALTANTE: $' . number_format(abs($diferencia), 0, ',', '.');
            }
        @endphp
        
        <div class="diferencia {{ $claseDiv }}">
            {{ $textoDif }}
        </div>
    </div>

    @if($caja->observaciones)
    <div class="observaciones">
        <strong>OBSERVACIONES:</strong><br>
        {{ $caja->observaciones }}
    </div>
    @endif

    <!-- Firma -->
    <div class="firma-section">
        <div class="firma-line"></div>
        <div>FIRMA DEL CAJERO</div>
        <div style="margin-top: 5px;">{{ $caja->usuario->name ?? '' }}</div>
    </div>


    <!-- Pie de página -->
    <div class="footer">
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
