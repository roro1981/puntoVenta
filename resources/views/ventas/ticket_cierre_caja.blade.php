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
            padding: 4px 0;
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
        
        table td {
            padding: 2px 0;
            vertical-align: top;
            overflow: hidden;
            word-wrap: break-word;
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
            margin: 8px auto;
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
            margin: 8px auto;
            padding: 6px 4px;
            border: 1px solid #000;
            font-size: 11px;
            word-wrap: break-word;
            width: 98%;
            font-weight: bold;
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
    <!-- Encabezado con logo -->
    <div class="header">
        @if(isset($corporateData['logo_enterprise']) && $corporateData['logo_enterprise'] && $corporateData['logo_enterprise'] != '/img/fotos_prod/sin_imagen.jpg' && file_exists(public_path($corporateData['logo_enterprise'])))
            <img src="{{ public_path($corporateData['logo_enterprise']) }}" alt="Logo" class="logo">
        @endif
    </div>

    <!-- Título -->
    <div class="title-box">
        CIERRE DE CAJA N° {{ sprintf('%04d', $caja->id) }}
    </div>

    <!-- Información de Caja -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">USUARIO:</span>
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
    
    @if($desglose['mixto'] > 0)
    <div class="info-row">
        <span>MIXTO</span>
        <span>${{ number_format($desglose['mixto'], 0, ',', '.') }}</span>
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
        
        <div class="total-esperado">
            <div class="total-row">
                <span class="total-label">MONTO ESPERADO:</span>
                <span class="total-label">${{ number_format($caja->monto_inicial + $totalVentas, 0, ',', '.') }}</span>
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

    <!-- Advertencia -->
    <div class="warning-text">
        ESTE DOCUMENTO NO ES VÁLIDO COMO COMPROBANTE TRIBUTARIO
    </div>

    <!-- Pie de página -->
    <div class="footer">
        @if(isset($corporateData['name_enterprise']) && $corporateData['name_enterprise'])
        <p>{{ $corporateData['name_enterprise'] }}</p>
        @endif
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
