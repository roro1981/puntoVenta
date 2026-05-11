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
            padding: 0 1mm;
            box-sizing: border-box;
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
            padding: 0 1mm;
            box-sizing: border-box;
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
    <!-- Encabezado con logo y datos empresa -->
    <div class="header">
        <?php
            $logoSrc = null;
            if (isset($corporateData['logo_enterprise']) && $corporateData['logo_enterprise'] && $corporateData['logo_enterprise'] !== '/img/fotos_prod/sin_imagen.jpg') {
                $logoPath = realpath(public_path(ltrim($corporateData['logo_enterprise'], '/')));
                if ($logoPath && file_exists($logoPath)) {
                    $mime = mime_content_type($logoPath);
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                }
            }
        ?>
        <?php if($logoSrc): ?>
            <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="logo">
        <?php endif; ?>

        <div style="font-weight:bold;font-size:13px;margin:4px 0 2px;"><?php echo e($corporateData['name_enterprise'] ?? ''); ?></div>

        <?php if(isset($corporateData['fantasy_name_enterprise']) && $corporateData['fantasy_name_enterprise']): ?>
        <div style="font-size:10px;font-weight:bold;"><?php echo e($corporateData['fantasy_name_enterprise']); ?></div>
        <?php endif; ?>

        <?php if(isset($corporateData['address_enterprise']) && $corporateData['address_enterprise']): ?>
        <div style="font-size:9px;"><?php echo e($corporateData['address_enterprise']); ?></div>
        <?php endif; ?>

        <?php if(isset($corporateData['comuna_enterprise']) && $corporateData['comuna_enterprise']): ?>
        <div style="font-size:9px;"><?php echo e($corporateData['comuna_enterprise']); ?></div>
        <?php endif; ?>

        <?php if(isset($corporateData['phone_enterprise']) && $corporateData['phone_enterprise']): ?>
        <div style="font-size:9px;">Tel: <?php echo e($corporateData['phone_enterprise']); ?></div>
        <?php endif; ?>
    </div>

    <!-- Título -->
    <div class="title-box">
        CIERRE DE CAJA N° <?php echo e(sprintf('%04d', $caja->id)); ?>

    </div>

    <!-- Información de Caja -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">USUARIO:</span>
            <span><?php echo e($caja->usuario->name ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">APERTURA:</span>
            <span><?php echo e($caja->fecha_apertura ? $caja->fecha_apertura->format('d/m/Y H:i:s') : 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">CIERRE:</span>
            <span><?php echo e($caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i:s') : 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">DURACION:</span>
            <span><?php echo e(($caja->fecha_apertura && $caja->fecha_cierre) ? $caja->fecha_apertura->diffInHours($caja->fecha_cierre) . ' hrs ' . ($caja->fecha_apertura->diffInMinutes($caja->fecha_cierre) % 60) . ' min' : 'N/A'); ?></span>
        </div>
    </div>

    <div class="separator"></div>

    <!-- Resumen de Ventas -->
    <div class="section-title">RESUMEN DE VENTAS</div>
    
    <div class="info-row">
        <span>CANTIDAD DE VENTAS:</span>
        <span class="info-label"><?php echo e($cantidadVentas); ?></span>
    </div>
    <div class="info-row">
        <span>TOTAL VENTAS:</span>
        <span class="info-label">$<?php echo e(number_format($totalVentas, 0, ',', '.')); ?></span>
    </div>

    <div class="separator"></div>

    <!-- Desglose por Forma de Pago -->
    <div class="section-title">DESGLOSE POR FORMA DE PAGO</div>
    
    <?php if($desglose['efectivo'] > 0): ?>
    <div class="info-row">
        <span>EFECTIVO</span>
        <span>$<?php echo e(number_format($desglose['efectivo'], 0, ',', '.')); ?></span>
    </div>
    <?php endif; ?>
    
    <?php if($desglose['tarjeta_debito'] > 0): ?>
    <div class="info-row">
        <span>TARJETA DEBITO</span>
        <span>$<?php echo e(number_format($desglose['tarjeta_debito'], 0, ',', '.')); ?></span>
    </div>
    <?php endif; ?>
    
    <?php if($desglose['tarjeta_credito'] > 0): ?>
    <div class="info-row">
        <span>TARJETA CREDITO</span>
        <span>$<?php echo e(number_format($desglose['tarjeta_credito'], 0, ',', '.')); ?></span>
    </div>
    <?php endif; ?>
    
    <?php if($desglose['transferencia'] > 0): ?>
    <div class="info-row">
        <span>TRANSFERENCIA</span>
        <span>$<?php echo e(number_format($desglose['transferencia'], 0, ',', '.')); ?></span>
    </div>
    <?php endif; ?>
    
    <?php if($desglose['cheque'] > 0): ?>
    <div class="info-row">
        <span>CHEQUE</span>
        <span>$<?php echo e(number_format($desglose['cheque'], 0, ',', '.')); ?></span>
    </div>
    <?php endif; ?>
    
    <?php if($desglose['mixto'] > 0): ?>
    <div class="info-row">
        <span>MIXTO</span>
        <span>$<?php echo e(number_format($desglose['mixto'], 0, ',', '.')); ?></span>
    </div>
    <?php endif; ?>

    <div class="separator"></div>

    <?php if(isset($retiros) && $retiros->count() > 0): ?>
    <!-- Retiros de Caja -->
    <div class="section-title">RETIROS DE CAJA</div>

    <table style="width:100%;font-size:9px;border-collapse:collapse;margin:2px 0;">
        <thead>
            <tr style="border-bottom:1px dotted #999;">
                <th style="text-align:left;padding:1px 0;">Motivo</th>
                <th style="text-align:center;padding:1px 2px;">Fecha/Hora</th>
                <th style="text-align:right;padding:1px 0;">Monto</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $retiros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $retiro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td style="padding:2px 0;overflow:hidden;word-break:break-word;max-width:30mm;"><?php echo e(strtoupper($retiro->motivo)); ?></td>
                <td style="text-align:center;padding:2px 2px;white-space:nowrap;"><?php echo e(\Carbon\Carbon::parse($retiro->created_at)->format('d/m/Y H:i')); ?></td>
                <td style="text-align:right;padding:2px 0;white-space:nowrap;">-$<?php echo e(number_format($retiro->monto, 0, ',', '.')); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr style="border-top:1px solid #000;">
                <td colspan="2" style="padding:2px 0;"><strong>TOTAL RETIROS:</strong></td>
                <td style="text-align:right;padding:2px 0;"><strong>-$<?php echo e(number_format($totalRetiros ?? 0, 0, ',', '.')); ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="separator"></div>
    <?php endif; ?>

    <!-- Totales de Cierre -->
    <div class="totals-section">
        <div class="total-row">
            <span class="total-label">MONTO INICIAL:</span>
            <span>$<?php echo e(number_format($caja->monto_inicial, 0, ',', '.')); ?></span>
        </div>

        <div class="total-row">
            <span class="total-label">TOTAL VENTAS:</span>
            <span>$<?php echo e(number_format($totalVentas, 0, ',', '.')); ?></span>
        </div>

        <?php if(isset($totalRetiros) && $totalRetiros > 0): ?>
        <div class="total-row" style="color:#c0392b;">
            <span class="total-label">TOTAL RETIROS:</span>
            <span>-$<?php echo e(number_format($totalRetiros, 0, ',', '.')); ?></span>
        </div>
        <?php endif; ?>

        <div class="total-esperado">
            <div class="total-row">
                <span class="total-label">MONTO ESPERADO:</span>
                <span class="total-label">$<?php echo e(number_format($caja->monto_inicial + $totalVentas - ($totalRetiros ?? 0), 0, ',', '.')); ?></span>
            </div>
        </div>
        
        <div class="total-declarado">
            <div class="total-row">
                <span class="total-label">MONTO DECLARADO:</span>
                <span class="total-label">$<?php echo e(number_format($caja->monto_final_declarado, 0, ',', '.')); ?></span>
            </div>
        </div>
        
        <?php
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
        ?>
        
        <div class="diferencia <?php echo e($claseDiv); ?>">
            <?php echo e($textoDif); ?>

        </div>
    </div>

    <?php if($caja->observaciones): ?>
    <div class="observaciones">
        <strong>OBSERVACIONES:</strong><br>
        <?php echo e($caja->observaciones); ?>

    </div>
    <?php endif; ?>

    <!-- Firma -->
    <div class="firma-section">
        <div class="firma-line"></div>
        <div>FIRMA DEL CAJERO</div>
        <div style="margin-top: 5px;"><?php echo e($caja->usuario->name ?? ''); ?></div>
    </div>

    <!-- Advertencia -->
    <div class="warning-text">
        ESTE DOCUMENTO NO ES VÁLIDO COMO COMPROBANTE TRIBUTARIO
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <?php if(isset($corporateData['name_enterprise']) && $corporateData['name_enterprise']): ?>
        <p><?php echo e($corporateData['name_enterprise']); ?></p>
        <?php endif; ?>
        <p><?php echo e(now()->format('d/m/Y H:i:s')); ?></p>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/ventas/ticket_cierre_caja.blade.php ENDPATH**/ ?>