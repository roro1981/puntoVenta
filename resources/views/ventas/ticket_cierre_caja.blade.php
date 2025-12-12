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
            width: 80mm;
            padding: 3mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        
        .logo {
            max-width: 60mm;
            max-height: 25mm;
            margin: 0 auto 8px;
            display: block;
            height: auto;
        }
        
        .title-box {
            background: #000;
            color: #fff;
            padding: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin: 8px 0;
        }
        
        .info-section {
            margin: 8px 0;
            font-size: 10px;
            background: #f5f5f5;
            padding: 6px;
            border-radius: 3px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
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
        }
        
        table td {
            padding: 3px 0;
            vertical-align: top;
        }
        
        table td:last-child {
            text-align: right;
        }
        
        .totals-section {
            margin-top: 8px;
            border-top: 2px solid #000;
            padding-top: 6px;
            background: #f9f9f9;
            padding: 8px 6px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .total-esperado {
            font-size: 12px;
            font-weight: bold;
            background: #f0f0f0;
            padding: 6px;
            margin: 6px 0;
        }
        
        .total-declarado {
            font-size: 12px;
            font-weight: bold;
            padding: 6px;
            margin: 6px 0;
        }
        
        .diferencia {
            font-size: 13px;
            font-weight: bold;
            padding: 8px;
            margin: 8px 0;
            text-align: center;
            border: 2px solid #000;
        }
        
        .diferencia.positiva {
            background: #d4edda;
            color: #155724;
        }
        
        .diferencia.negativa {
            background: #f8d7da;
            color: #721c24;
        }
        
        .diferencia.exacta {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .observaciones {
            margin: 8px 0;
            padding: 6px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            font-size: 10px;
            word-wrap: break-word;
        }
        
        .footer {
            text-align: center;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 2px dashed #000;
            font-size: 10px;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        .firma-section {
            margin-top: 12px;
            text-align: center;
            font-size: 10px;
        }
        
        .firma-line {
            border-top: 1px solid #000;
            width: 60%;
            margin: 20px auto 5px;
        }
        
        .warning-text {
            margin-top: 10px;
            padding: 5px;
            border-top: 1px dashed #000;
            padding-top: 5px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Encabezado con logo -->
    <div class="header">
        @if(isset($corporateData['logo_enterprise']) && $corporateData['logo_enterprise'] && $corporateData['logo_enterprise'] != '/img/fotos_prod/sin_imagen.jpg')
            <img src="{{ public_path($corporateData['logo_enterprise']) }}" alt="Logo" class="logo">
        @endif
    </div>

    <!-- Título -->
    <div class="title-box">
        CIERRE DE CAJA Nº {{ str_pad($caja->id, 4, '0', STR_PAD_LEFT) }}
    </div>

    <!-- Información de Caja -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Usuario:</span>
            <span>{{ $caja->usuario->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Apertura:</span>
            <span>{{ $caja->fecha_apertura->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cierre:</span>
            <span>{{ $caja->fecha_cierre->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Duración:</span>
            <span>{{ $caja->fecha_apertura->diffInHours($caja->fecha_cierre) }} hrs {{ $caja->fecha_apertura->diffInMinutes($caja->fecha_cierre) % 60 }} min</span>
        </div>
    </div>

    <div class="separator"></div>

    <!-- Resumen de Ventas -->
    <div class="section-title">RESUMEN DE VENTAS</div>
    
    <div class="info-row">
        <span>Cantidad de Ventas:</span>
        <span class="info-label">{{ $cantidadVentas }}</span>
    </div>
    <div class="info-row">
        <span>Total Ventas:</span>
        <span class="info-label">${{ number_format($totalVentas, 0, ',', '.') }}</span>
    </div>

    <div class="separator"></div>

    <!-- Desglose por Forma de Pago -->
    <div class="section-title">DESGLOSE POR FORMA DE PAGO</div>
    
    <table>
        @if($desglose['efectivo'] > 0)
        <tr>
            <td>Efectivo</td>
            <td class="text-right">${{ number_format($desglose['efectivo'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['tarjeta_debito'] > 0)
        <tr>
            <td>Tarjeta Débito</td>
            <td class="text-right">${{ number_format($desglose['tarjeta_debito'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['tarjeta_credito'] > 0)
        <tr>
            <td>Tarjeta Crédito</td>
            <td class="text-right">${{ number_format($desglose['tarjeta_credito'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['transferencia'] > 0)
        <tr>
            <td>Transferencia</td>
            <td class="text-right">${{ number_format($desglose['transferencia'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['cheque'] > 0)
        <tr>
            <td>Cheque</td>
            <td class="text-right">${{ number_format($desglose['cheque'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['mixto'] > 0)
        <tr>
            <td>Mixto</td>
            <td class="text-right">${{ number_format($desglose['mixto'], 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <div class="separator"></div>

    <!-- Totales de Cierre -->
    <div class="totals-section">
        <div class="total-row">
            <span class="total-label">Monto Inicial:</span>
            <span>${{ number_format($caja->monto_inicial, 0, ',', '.') }}</span>
        </div>
        
        <div class="total-row">
            <span class="total-label">Total Ventas:</span>
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
        <strong>Observaciones:</strong><br>
        {{ $caja->observaciones }}
    </div>
    @endif

    <!-- Firma -->
    <div class="firma-section">
        <div class="firma-line"></div>
        <div>Firma del Cajero</div>
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
