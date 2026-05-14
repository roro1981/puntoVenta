<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alerta Stock Mínimo</title>
</head>
<body style="margin:0;padding:0;background:#f2f4f7;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333;">

  <!-- Wrapper -->
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f7;padding:30px 0;">
    <tr>
      <td align="center">
        <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.10);">

          <!-- Header rojo alerta -->
          <tr>
            <td style="background:linear-gradient(135deg,#c0392b 0%,#e74c3c 100%);padding:28px 32px;text-align:center;">
              <p style="margin:0 0 6px 0;font-size:13px;color:rgba(255,255,255,0.80);letter-spacing:1.5px;text-transform:uppercase;">Sistema de Gestión — Alerta Automática</p>
              <h1 style="margin:0;font-size:26px;color:#ffffff;font-weight:700;line-height:1.3;">
                Reporte Diario de Stock Mínimo
              </h1>
              <p style="margin:10px 0 0;font-size:15px;color:rgba(255,255,255,0.90);"><?php echo e($empresaNombre); ?></p>
            </td>
          </tr>

          <!-- Cuerpo -->
          <tr>
            <td style="padding:28px 32px;">

              <!-- Banner resumen -->
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff5f5;border:1px solid #f5c6c2;border-radius:8px;margin-bottom:24px;">
                <tr>
                  <td style="padding:16px 20px;">
                    <p style="margin:0;font-size:15px;color:#c0392b;font-weight:700;">
                      Al cierre del día se detectaron <?php echo e(count($alertas)); ?> producto<?php echo e(count($alertas) > 1 ? 's' : ''); ?> en o por debajo de su stock mínimo configurado.
                    </p>
                    <p style="margin:8px 0 0;font-size:13px;color:#7b241c;">
                      <strong>Fecha del reporte:</strong>&nbsp;<?php echo e($fechaReporte); ?>

                    </p>
                  </td>
                </tr>
              </table>

              <!-- Intro -->
              <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#444;">
                El sistema <strong>PVenta</strong> generó este resumen consolidado diario con los productos que ya se encuentran en estado crítico.
                Se recomienda revisar reposición y abastecimiento para evitar quiebres de stock al inicio de la jornada.
              </p>

              <!-- Tabla de productos -->
              <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
                <thead>
                  <tr style="background:#c0392b;color:#ffffff;">
                    <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;border-radius:4px 0 0 0;">#</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;">Código</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;">Producto</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;">Categoría</th>
                    <th style="padding:10px 12px;text-align:center;font-size:12px;font-weight:700;">Stock actual</th>
                    <th style="padding:10px 12px;text-align:center;font-size:12px;font-weight:700;border-radius:0 4px 0 0;">Stock mínimo</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $__currentLoopData = $alertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr style="background:<?php echo e($loop->even ? '#fff5f5' : '#ffffff'); ?>;border-bottom:1px solid #f0d0ce;">
                    <td style="padding:10px 12px;font-size:13px;color:#888;"><?php echo e($loop->iteration); ?></td>
                    <td style="padding:10px 12px;font-size:12px;color:#555;font-family:monospace;"><?php echo e($alerta['codigo'] ?? '—'); ?></td>
                    <td style="padding:10px 12px;font-size:13px;font-weight:600;color:#222;"><?php echo e($alerta['producto']); ?></td>
                    <td style="padding:10px 12px;font-size:12px;color:#666;"><?php echo e($alerta['categoria'] ?? '—'); ?></td>
                    <td style="padding:10px 12px;text-align:center;font-size:14px;font-weight:700;color:#c0392b;">
                      <?php echo e(number_format($alerta['stock_actual'], 2, ',', '.')); ?>

                      <?php if(!empty($alerta['unidad'])): ?> <span style="font-size:11px;color:#888;"><?php echo e($alerta['unidad']); ?></span> <?php endif; ?>
                    </td>
                    <td style="padding:10px 12px;text-align:center;font-size:13px;color:#888;">
                      <?php echo e(number_format($alerta['stock_minimo'], 2, ',', '.')); ?>

                    </td>
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
              </table>

              <!-- Indicaciones -->
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#fef9e7;border:1px solid #f9e3a0;border-radius:8px;margin-bottom:24px;">
                <tr>
                  <td style="padding:16px 20px;">
                    <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#856404;">📋 Acciones recomendadas</p>
                    <ul style="margin:0;padding-left:18px;font-size:13px;color:#665000;line-height:1.8;">
                      <li>Verificar la disponibilidad de los productos en bodega.</li>
                      <li>Contactar a proveedores para reabastecer el stock.</li>
                      <li>Revisar el módulo <em>Compras → Ingresos</em> para registrar nuevas entradas.</li>
                      <li>Si corresponde, ajustar el stock mínimo en el módulo <em>Almacén → Productos</em>.</li>
                    </ul>
                  </td>
                </tr>
              </table>

              <!-- Nota sistema -->
              <p style="margin:0;font-size:12px;color:#999;line-height:1.6;border-top:1px solid #eee;padding-top:16px;">
                Este correo fue generado automáticamente por el sistema <strong>PVenta</strong> como reporte diario consolidado de stock mínimo.
                No responda este mensaje. Si desea desactivar estas alertas, comuníquese con su administrador del sistema.
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f8f9fa;padding:16px 32px;text-align:center;border-top:1px solid #e9ecef;">
              <p style="margin:0;font-size:12px;color:#aaa;">
                <strong style="color:#c0392b;">PVenta</strong> — Sistema de Punto de Venta &nbsp;|&nbsp; <?php echo e($fechaReporte); ?>

              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/emails/stock_minimo_alert.blade.php ENDPATH**/ ?>