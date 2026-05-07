<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Preventa Nº <?php echo e($numeroPreventa); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
            text-align: center;
        }

        .header .fantasy-name {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 4px;
            text-align: center;
        }

        .header p {
            font-size: 11px;
            margin: 2px 0;
            text-align: center;
        }

        .info-section {
            margin: 8px 0;
            font-size: 11px;
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
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 6px;
            padding: 4px;
            background: #fff;
            border: 2px solid #000;
            border-radius: 3px;
            letter-spacing: 2px;
        }

        .barcode-section {
            text-align: center;
            margin: 8px 0;
            padding: 4px 0;
        }

        .barcode-section svg {
            display: block;
            margin: 0 auto;
        }

        .barcode-text {
            font-size: 10px;
            text-align: center;
            letter-spacing: 3px;
            margin-top: 3px;
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
            padding-left: 1mm;
            padding-right: 1mm;
            border-bottom: 1px dotted #ccc;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
            color: #333;
        }

        .product-details {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #666;
            padding: 0 1mm;
        }

        .product-details .left {
            display: flex;
            gap: 15px;
        }

        .product-discount {
            font-size: 10px;
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
            font-size: 12px;
            padding: 0 1mm;
        }

        .totals .total-final {
            font-size: 15px;
            font-weight: bold;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #000;
        }

        .footer {
            text-align: center;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 2px dashed #000;
            font-size: 11px;
        }

        .footer p { margin: 3px 0; text-align: center; }

        .footer .thanks {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .pending-notice {
            text-align: center;
            font-size: 11px;
            font-style: italic;
            margin: 6px 0;
            color: #555;
        }
    </style>
</head>
<body>
    <!-- Encabezado empresa -->
    <div class="header">
        <?php if(isset($corporateData['logo_enterprise']) && $corporateData['logo_enterprise'] && $corporateData['logo_enterprise'] != '/img/fotos_prod/sin_imagen.jpg'): ?>
        <div class="logo-container">
            <img src="<?php echo e(public_path($corporateData['logo_enterprise'])); ?>" alt="Logo">
        </div>
        <?php endif; ?>

        <h1><?php echo e($corporateData['name_enterprise'] ?? 'MI NEGOCIO'); ?></h1>

        <?php if(isset($corporateData['fantasy_name_enterprise']) && $corporateData['fantasy_name_enterprise']): ?>
        <div class="fantasy-name"><?php echo e($corporateData['fantasy_name_enterprise']); ?></div>
        <?php endif; ?>

        <?php if(isset($corporateData['address_enterprise']) && $corporateData['address_enterprise']): ?>
        <p><?php echo e($corporateData['address_enterprise']); ?></p>
        <?php endif; ?>

        <?php if(isset($corporateData['phone_enterprise']) && $corporateData['phone_enterprise']): ?>
        <p>Tel: <?php echo e($corporateData['phone_enterprise']); ?></p>
        <?php endif; ?>
    </div>

    <!-- Número de preventa -->
    <div class="info-section">
        <div class="ticket-number">PREVENTA Nº <?php echo e($numeroPreventa); ?></div>
        <div class="row">
            <span>Fecha:</span>
            <span><?php echo e(\Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i')); ?></span>
        </div>
        <div class="row">
            <span>Vendedor:</span>
            <span><?php echo e($venta->usuario->name ?? 'N/A'); ?></span>
        </div>
    </div>

    <!-- Código de barras -->
    <div class="barcode-section">
        <img src="data:image/svg+xml;base64,<?php echo e(base64_encode($barcodeSvg)); ?>" style="display:block;margin:0 auto;max-width:100%;height:auto;" alt="barcode">
        <div class="barcode-text"><?php echo e($numeroPreventa); ?></div>
    </div>

    <div class="separator"></div>

    <!-- Detalle productos -->
    <div class="products-section">
        <?php $__currentLoopData = $venta->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="product-item">
            <div class="product-name"><?php echo e($detalle->descripcion_producto); ?></div>
            <div class="product-details">
                <div class="left">
                    <span><?php echo e(rtrim(rtrim(number_format($detalle->cantidad, 2, '.', ''), '0'), '.')); ?> × $<?php echo e(number_format($detalle->precio_unitario, 0, ',', '.')); ?></span>
                </div>
                <div class="right">
                    <strong>$<?php echo e(number_format($detalle->subtotal_linea, 0, ',', '.')); ?></strong>
                </div>
            </div>
            <?php if($detalle->descuento_porcentaje > 0): ?>
            <div class="product-discount">Descuento: <?php echo e($detalle->descuento_porcentaje); ?>%</div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Totales -->
    <div class="totals">
        <?php if($venta->total_descuentos > 0): ?>
        <div class="row">
            <span>Subtotal:</span>
            <span>$<?php echo e(number_format($venta->total + $venta->total_descuentos, 0, ',', '.')); ?></span>
        </div>
        <div class="row" style="color: #d9534f; font-weight: 600;">
            <span>Descuentos:</span>
            <span>-$<?php echo e(number_format($venta->total_descuentos, 0, ',', '.')); ?></span>
        </div>
        <?php endif; ?>

        <div class="row total-final">
            <span>TOTAL PREVENTA:</span>
            <span>$<?php echo e(number_format($venta->total, 0, ',', '.')); ?></span>
        </div>
    </div>

    <!-- Aviso pago pendiente -->
    <div class="pending-notice">
        * Pago pendiente de confirmación *<br>
        Presente este ticket en caja para cancelar
    </div>

    <!-- Pie -->
    <div class="footer">
        <p class="thanks">¡Gracias por su preferencia!</p>
        <?php if(isset($corporateData['website_enterprise']) && $corporateData['website_enterprise']): ?>
        <p><?php echo e($corporateData['website_enterprise']); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/ventas/ticket_preventa.blade.php ENDPATH**/ ?>