<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Cocina - <?php echo e($comanda->numero_comanda ?? ('#' . $comanda->id)); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.4;
            color: #000;
            width: 72mm;
            padding: 2mm 2mm 4mm 2mm;
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
            color: #333;
        }

        .obs-general {
            border: 2px solid #000;
            padding: 5px 6px;
            margin: 4px 0;
            font-size: 12px;
            font-weight: bold;
        }

        .obs-general-title {
            text-align: center;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .total-items {
            text-align: right;
            font-size: 11px;
            margin-top: 6px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 6px;
            border-top: 2px dashed #000;
            font-size: 10px;
        }

        .print-time {
            text-align: center;
            font-size: 10px;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <div class="title-box">*** COCINA ***</div>

    <div class="comanda-num">
        <?php echo e($comanda->numero_comanda ?? ('#' . str_pad($comanda->id, 4, '0', STR_PAD_LEFT))); ?>

    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">MESA:</span>
            <span><?php echo e(optional($comanda->mesa)->nombre ?? 'N/A'); ?></span>
        </div>
        <?php if(optional($comanda->garzon)->nombre_completo): ?>
        <div class="info-row">
            <span class="info-label">GARZÓN:</span>
            <span><?php echo e($comanda->garzon->nombre_completo); ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">COMENSALES:</span>
            <span><?php echo e((int)($comanda->comensales ?? 0)); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">HORA:</span>
            <span><?php echo e($comanda->fecha_apertura ? $comanda->fecha_apertura->format('H:i') : now()->format('H:i')); ?></span>
        </div>
    </div>

    <div class="separator-solid"></div>
    <div class="section-title">PEDIDO</div>
    <div class="separator"></div>

    <?php if(!empty($comanda->observaciones)): ?>
        <div class="obs-general">
            <div class="obs-general-title">*** NOTA ***</div>
            <div><?php echo e($comanda->observaciones); ?></div>
        </div>
        <div class="separator"></div>
    <?php endif; ?>

    <?php $__currentLoopData = $comanda->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $esReceta = ($detalle->tipo_item ?? 'PRODUCTO') === 'RECETA';
            $descripcion = $esReceta
                ? (optional($detalle->receta)->nombre ?? optional($detalle->producto)->descripcion ?? 'Receta')
                : (optional($detalle->producto)->descripcion ?? optional($detalle->producto)->nom_prod ?? 'Producto');
            $cantidad = (int) $detalle->cantidad;
        ?>
        <div class="item">
            <div class="item-main">
                <span class="item-cant"><?php echo e($cantidad); ?></span>
                <span class="item-x">x</span>
                <span class="item-desc"><?php echo e(mb_strtoupper($descripcion, 'UTF-8')); ?></span>
            </div>
            <?php if(!empty($detalle->observaciones)): ?>
                <div class="item-obs">↳ <?php echo e($detalle->observaciones); ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <div class="separator"></div>
    <div class="total-items">
        Total ítems: <?php echo e($comanda->detalles->sum('cantidad')); ?>

    </div>

    <div class="footer">
        <p>Impreso: <?php echo e(now()->format('d/m/Y H:i:s')); ?></p>
    </div>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/restaurant/ticket_cocina.blade.php ENDPATH**/ ?>