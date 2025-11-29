<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cierre de Caja</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 226.77pt; /* 80mm */
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            max-width: 60mm;
            max-height: 25mm;
            margin: 0 auto 10px;
            display: block;
        }
        .company-name {
            font-weight: bold;
            font-size: 14px;
            margin: 3px 0;
        }
        .company-info {
            font-size: 10px;
            margin: 2px 0;
        }
        .title-box {
            background: #000;
            color: #fff;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 15px 0;
        }
        .info-section {
            margin: 10px 0;
            padding: 5px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        .info-label {
            font-weight: bold;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin: 10px 0 5px 0;
            text-align: center;
            text-decoration: underline;
        }
        table {
            width: 100%;
            margin: 5px 0;
            border-collapse: collapse;
        }
        table td {
            padding: 3px 0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 12px;
        }
        .total-label {
            font-weight: bold;
        }
        .total-esperado {
            font-size: 13px;
            font-weight: bold;
            background: #f0f0f0;
            padding: 5px;
            margin: 5px 0;
        }
        .total-declarado {
            font-size: 13px;
            font-weight: bold;
            padding: 5px;
            margin: 5px 0;
        }
        .diferencia {
            font-size: 14px;
            font-weight: bold;
            padding: 8px;
            margin: 10px 0;
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
            margin: 10px 0;
            padding: 8px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
        }
        .firma-section {
            margin-top: 30px;
            text-align: center;
        }
        .firma-line {
            border-top: 1px solid #000;
            width: 60%;
            margin: 40px auto 5px;
        }
        .warning-text {
            margin-top: 15px;
            padding: 5px;
            border: 1px solid #000;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Encabezado con logo y datos corporativos -->
    <div class="header">
        @if(isset($corporateData['logo_enterprise']) && $corporateData['logo_enterprise'] !== '/img/fotos_prod/sin_imagen.jpg')
            <img src="{{ public_path($corporateData['logo_enterprise']) }}" alt="Logo" class="logo">
        @endif
        
        @if(isset($corporateData['name_enterprise']) && $corporateData['name_enterprise'])
            <div class="company-name">{{ $corporateData['name_enterprise'] }}</div>
        @endif
        
        @if(isset($corporateData['fantasy_name_enterprise']) && $corporateData['fantasy_name_enterprise'])
            <div class="company-info">{{ $corporateData['fantasy_name_enterprise'] }}</div>
        @endif
        
        @if(isset($corporateData['address_enterprise']) && $corporateData['address_enterprise'])
            <div class="company-info">{{ $corporateData['address_enterprise'] }}</div>
        @endif
        
        @if(isset($corporateData['comuna_enterprise']) && $corporateData['comuna_enterprise'])
            <div class="company-info">{{ $corporateData['comuna_enterprise'] }}</div>
        @endif
        
        @if(isset($corporateData['phone_enterprise']) && $corporateData['phone_enterprise'])
            <div class="company-info">Tel: {{ $corporateData['phone_enterprise'] }}</div>
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
            <td>💵 Efectivo</td>
            <td class="text-right">${{ number_format($desglose['efectivo'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['tarjeta_debito'] > 0)
        <tr>
            <td>🏦 Tarjeta Débito</td>
            <td class="text-right">${{ number_format($desglose['tarjeta_debito'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['tarjeta_credito'] > 0)
        <tr>
            <td>💳 Tarjeta Crédito</td>
            <td class="text-right">${{ number_format($desglose['tarjeta_credito'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['transferencia'] > 0)
        <tr>
            <td>🔄 Transferencia</td>
            <td class="text-right">${{ number_format($desglose['transferencia'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['cheque'] > 0)
        <tr>
            <td>📋 Cheque</td>
            <td class="text-right">${{ number_format($desglose['cheque'], 0, ',', '.') }}</td>
        </tr>
        @endif
        
        @if($desglose['mixto'] > 0)
        <tr>
            <td>🔀 Mixto</td>
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
