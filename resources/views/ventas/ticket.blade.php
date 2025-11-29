<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - {{ $venta->numero_venta }}</title>
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
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
        }
        
        .header .logo-container {
            margin-bottom: 8px;
            text-align: center;
        }
        
        .header .logo-container img {
            max-width: 60mm;
            max-height: 25mm;
            height: auto;
        }
        
        .header h1 {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 3px;
            text-align: center;
        }
        
        .header .fantasy-name {
            font-size: 12px;
            font-weight: normal;
            margin-bottom: 4px;
            text-align: center;
        }
        
        .header p {
            font-size: 10px;
            margin: 2px 0;
            text-align: center;
        }
        
        .info-section {
            margin: 8px 0;
            font-size: 10px;
            background: #f5f5f5;
            padding: 6px;
            border-radius: 3px;
        }
        
        .info-section .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        
        .info-section .ticket-number {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
            padding: 4px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        
        .separator {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        
        .products-section {
            margin: 8px 0;
        }
        
        .product-item {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dotted #ccc;
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .product-name {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
            color: #333;
        }
        
        .product-details {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #666;
        }
        
        .product-details .left {
            display: flex;
            gap: 15px;
        }
        
        .product-discount {
            font-size: 9px;
            color: #d9534f;
            margin-top: 2px;
            font-style: italic;
        }
        
        .totals {
            margin-top: 8px;
            border-top: 2px solid #000;
            padding-top: 6px;
            background: #f9f9f9;
            padding: 8px 6px;
        }
        
        .totals .row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        
        .totals .total-final {
            font-size: 15px;
            font-weight: bold;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #000;
        }
        
        .payment-methods {
            margin-top: 8px;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 6px;
        }
        
        .payment-methods .title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 11px;
        }
        
        .payment-methods .method {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            padding: 2px 0;
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
            text-align: center;
        }
        
        .footer .thanks {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        @if(isset($corporateData['logo_enterprise']) && $corporateData['logo_enterprise'] && $corporateData['logo_enterprise'] != '/img/fotos_prod/sin_imagen.jpg')
        <div class="logo-container">
            <img src="{{ public_path($corporateData['logo_enterprise']) }}" alt="Logo">
        </div>
        @endif
        
        <h1>{{ $corporateData['name_enterprise'] ?? 'MI NEGOCIO' }}</h1>
        
        @if(isset($corporateData['fantasy_name_enterprise']) && $corporateData['fantasy_name_enterprise'])
        <div class="fantasy-name">{{ $corporateData['fantasy_name_enterprise'] }}</div>
        @endif
        
        @if(isset($corporateData['address_enterprise']) && $corporateData['address_enterprise'])
        <p>{{ $corporateData['address_enterprise'] }}</p>
        @endif
        
        @if(isset($corporateData['comuna_enterprise']) && $corporateData['comuna_enterprise'])
        <p>{{ $corporateData['comuna_enterprise'] }}</p>
        @endif
        
        @if(isset($corporateData['phone_enterprise']) && $corporateData['phone_enterprise'])
        <p>Tel: {{ $corporateData['phone_enterprise'] }}</p>
        @endif
    </div>
    
    <!-- Información de la venta -->
    <div class="info-section">
        <div class="ticket-number">TICKET Nº {{ str_pad($venta->id, 4, '0', STR_PAD_LEFT) }}</div>
        <div class="row">
            <span>Fecha:</span>
            <span>{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span>Vendedor:</span>
            <span>{{ $venta->usuario->name ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="separator"></div>
    
    <!-- Productos -->
    <div class="products-section">
    @foreach($venta->detalles as $detalle)
        <div class="product-item">
            <div class="product-name">{{ $detalle->descripcion_producto }}</div>
            <div class="product-details">
                <div class="left">
                    <span>{{ rtrim(rtrim(number_format($detalle->cantidad, 2, '.', ''), '0'), '.') }} × ${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</span>
                </div>
                <div class="right">
                    <strong>${{ number_format($detalle->subtotal_linea, 0, ',', '.') }}</strong>
                </div>
            </div>
            @if($detalle->descuento_porcentaje > 0)
            <div class="product-discount">
                Descuento aplicado: {{ $detalle->descuento_porcentaje }}%
            </div>
            @endif
        </div>
    @endforeach
    </div>
    
    <!-- Totales -->
    <div class="totals">
        @if($venta->total_descuentos > 0)
        <div class="row">
            <span>Subtotal:</span>
            <span>${{ number_format($venta->total + $venta->total_descuentos, 0, ',', '.') }}</span>
        </div>
        <div class="row" style="color: #d9534f; font-weight: 600;">
            <span>Descuentos Totales:</span>
            <span>-${{ number_format($venta->total_descuentos, 0, ',', '.') }}</span>
        </div>
        @endif
        
        <div class="row total-final">
            <span>TOTAL A PAGAR:</span>
            <span>${{ number_format($venta->total, 0, ',', '.') }}</span>
        </div>
    </div>
    
    <!-- Formas de pago -->
    <div class="payment-methods">
        <div class="title">Forma de Pago:</div>
        @if($venta->forma_pago === 'MIXTO')
            @foreach($venta->formasPago as $formaPago)
            <div class="method">
                <span>{{ str_replace('_', ' ', $formaPago->forma_pago) }}</span>
                <span>${{ number_format($formaPago->monto, 0, ',', '.') }}</span>
            </div>
            @endforeach
        @else
            <div class="method">
                <span>{{ str_replace('_', ' ', $venta->forma_pago) }}</span>
                <span>${{ number_format($venta->total, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>
    
    <!-- Pie de página -->
    <div class="footer">
        <p class="thanks">¡Gracias por su compra!</p>
        @if(isset($corporateData['name_enterprise']) && $corporateData['name_enterprise'])
        <p>{{ $corporateData['name_enterprise'] }}</p>
        @endif
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
        <p style="margin-top: 10px; font-size: 9px; font-weight: bold; border-top: 1px dashed #000; padding-top: 5px;">
            ESTE TICKET NO ES VÁLIDO COMO BOLETA O FACTURA
        </p>
    </div>
</body>
</html>
