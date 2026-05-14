<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperación de contraseña</title>
</head>
<body style="margin:0;padding:0;background:#eef1f5;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f5;padding:28px 12px;">
    <tr>
      <td align="center">
        <table width="620" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #dde3ea;box-shadow:0 4px 16px rgba(15,23,42,0.08);">

          <tr>
            <td style="background:#2b3747;padding:22px 28px;text-align:center;">
              <p style="margin:0 0 6px;color:#dbe3ee;font-size:12px;letter-spacing:1.2px;text-transform:uppercase;">Recuperación de acceso</p>
              <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;line-height:1.3;">Código de recuperación</h1>
              <p style="margin:8px 0 0;color:#dbe3ee;font-size:14px;"><?php echo e($empresaNombre); ?></p>
            </td>
          </tr>

          <tr>
            <td style="padding:26px 28px 22px;">
              <p style="margin:0 0 12px;font-size:14px;line-height:1.6;">Hola <strong><?php echo e($usuario); ?></strong>,</p>

              <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#334155;">
                Recibimos una solicitud para restablecer tu contraseña. Usa este código para continuar:
              </p>

              <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 18px;">
                <tr>
                  <td align="center" style="padding:18px 10px;background:#f5f7fa;border:1px dashed #b9c3d1;border-radius:10px;">
                    <span style="display:inline-block;font-size:34px;letter-spacing:7px;font-weight:800;color:#1e293b;"><?php echo e($codigo); ?></span>
                  </td>
                </tr>
              </table>

              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                <tr>
                  <td style="padding:12px 14px;font-size:13px;color:#475569;line-height:1.6;">
                    <strong>Importante:</strong>
                    Este código vence en <strong><?php echo e($expiraMinutos); ?> minutos</strong> y solo puede usarse una vez.
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#64748b;">
                Si tú no solicitaste este cambio, puedes ignorar este correo.
              </p>

              <p style="margin:0;font-size:12px;color:#94a3b8;">
                Enviado el <?php echo e($fechaEnvio); ?>

              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;">
              <p style="margin:0;font-size:12px;color:#94a3b8;">Este es un correo automático de seguridad. No respondas este mensaje.</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/emails/password_recovery_code.blade.php ENDPATH**/ ?>