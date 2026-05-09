<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; color: #263238; background: #f5f7fa; margin: 0; padding: 0; }
    .wrap { max-width: 560px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #1f5f8b 0%, #2980b9 100%); padding: 28px 28px 20px; }
    .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
    .header p  { color: rgba(255,255,255,.80); margin: 6px 0 0; font-size: 13px; }
    .body { padding: 28px; }
    .body p { font-size: 14px; line-height: 1.7; color: #263238; margin: 0 0 14px; }
    .attach-box { background: #f0f6fc; border: 1px solid #b8d8f0; border-radius: 6px;
                  padding: 12px 16px; margin: 18px 0; display: flex; align-items: center; gap: 12px; }
    .attach-box .icon { font-size: 28px; color: #1f5f8b; }
    .attach-box .info strong { display: block; font-size: 13px; color: #1f5f8b; }
    .attach-box .info span   { font-size: 12px; color: #7f8c8d; }
    .footer { background: #f5f7fa; padding: 14px 28px; border-top: 1px solid #e8edf2;
              font-size: 11px; color: #9aabb6; text-align: center; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1>Dashboard Gerencial</h1>
      <p><?php echo e($empresaNombre); ?></p>
    </div>
    <div class="body">
      <p>Hola,</p>
      <p>
        Adjunto encontrarás el <strong>resumen del Dashboard Gerencial</strong> de
        <strong><?php echo e($empresaNombre); ?></strong>, generado el
        <?php echo e(\Carbon\Carbon::now()->isoFormat('dddd D [de] MMMM [de] YYYY, HH:mm')); ?>.
      </p>
      <p>El archivo PDF contiene:</p>
      <ul style="font-size:14px;line-height:1.9;padding-left:18px;color:#263238;">
        <li>Estado general del negocio</li>
        <li>Métricas diarias, semanales y mensuales</li>
        <li>Gráficos de tendencia y evolución</li>
        <li>Tablas de rotación de inventario y sobrestock</li>
        <li>Análisis semestral y lectura gerencial</li>
        <li>Resumen de control interno</li>
      </ul>
      <div class="attach-box">
        <div class="icon">📎</div>
        <div class="info">
          <strong><?php echo e($filename); ?></strong>
          <span>Documento PDF adjunto</span>
        </div>
      </div>
      <p style="color:#7f8c8d;font-size:13px;">
        Este correo fue generado automáticamente desde el sistema PVenta.
      </p>
    </div>
    <div class="footer">
      &copy; <?php echo e(date('Y')); ?> Sistema PVenta &nbsp;·&nbsp; Generado el <?php echo e(now()->format('d/m/Y H:i')); ?>

    </div>
  </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/emails/dashboard_gerencial_pdf.blade.php ENDPATH**/ ?>