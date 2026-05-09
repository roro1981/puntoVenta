<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte Diario Stock Predictivo</title>
</head>
<body style="margin:0;padding:0;background:#f2f4f7;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f7;padding:30px 0;">
    <tr>
      <td align="center">
        <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.10);">
          <tr>
            <td style="background:linear-gradient(135deg,#b9770e 0%,#f39c12 100%);padding:28px 32px;text-align:center;">
              <p style="margin:0 0 6px 0;font-size:13px;color:rgba(255,255,255,0.80);letter-spacing:1.5px;text-transform:uppercase;">Sistema de Gestión — Alerta Automática</p>
              <h1 style="margin:0;font-size:26px;color:#ffffff;font-weight:700;line-height:1.3;">
                Reporte Diario de Stock Predictivo
              </h1>
              <p style="margin:10px 0 0;font-size:15px;color:rgba(255,255,255,0.90);">{{ $empresaNombre }}</p>
            </td>
          </tr>

          <tr>
            <td style="padding:28px 32px;">
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e8;border:1px solid #f5d28c;border-radius:8px;margin-bottom:24px;">
                <tr>
                  <td style="padding:16px 20px;">
                    <p style="margin:0;font-size:15px;color:#9c640c;font-weight:700;">
                      Se detectaron {{ count($alertas) }} producto{{ count($alertas) > 1 ? 's' : '' }} que, al ritmo actual de consumo, alcanzarán su stock mínimo pronto.
                    </p>
                    <p style="margin:8px 0 0;font-size:13px;color:#7e5109;">
                      <strong>Fecha del reporte:</strong>&nbsp;{{ $fechaReporte }}&nbsp;&nbsp;|&nbsp;&nbsp;
                      <strong>Ventana de análisis:</strong>&nbsp;{{ $diasAnalisis }} días&nbsp;&nbsp;|&nbsp;&nbsp;
                      <strong>Umbral:</strong>&nbsp;{{ number_format($umbralDias, 1, ',', '.') }} días
                    </p>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#444;">
                Este reporte anticipa productos que todavía no están en stock mínimo, pero que podrían llegar a ese nivel en pocos días si se mantiene el ritmo reciente de consumo.
              </p>

              <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
                <thead>
                  <tr style="background:#b9770e;color:#ffffff;">
                    <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;border-radius:4px 0 0 0;">#</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;">Código</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;">Producto</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;">Categoría</th>
                    <th style="padding:10px 12px;text-align:center;font-size:12px;font-weight:700;">Stock actual</th>
                    <th style="padding:10px 12px;text-align:center;font-size:12px;font-weight:700;">Stock mínimo</th>
                    <th style="padding:10px 12px;text-align:center;font-size:12px;font-weight:700;">Consumo diario</th>
                    <th style="padding:10px 12px;text-align:center;font-size:12px;font-weight:700;border-radius:0 4px 0 0;">Días estimados</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($alertas as $alerta)
                  <tr style="background:{{ $loop->even ? '#fffaf2' : '#ffffff' }};border-bottom:1px solid #f3dfbf;">
                    <td style="padding:10px 12px;font-size:13px;color:#888;">{{ $loop->iteration }}</td>
                    <td style="padding:10px 12px;font-size:12px;color:#555;font-family:monospace;">{{ $alerta['codigo'] ?? '—' }}</td>
                    <td style="padding:10px 12px;font-size:13px;font-weight:600;color:#222;">{{ $alerta['producto'] }}</td>
                    <td style="padding:10px 12px;font-size:12px;color:#666;">{{ $alerta['categoria'] ?? '—' }}</td>
                    <td style="padding:10px 12px;text-align:center;font-size:13px;font-weight:700;color:#9c640c;">
                      {{ number_format($alerta['stock_actual'], 2, ',', '.') }}
                      @if(!empty($alerta['unidad'])) <span style="font-size:11px;color:#888;">{{ $alerta['unidad'] }}</span> @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;font-size:13px;color:#888;">{{ number_format($alerta['stock_minimo'], 2, ',', '.') }}</td>
                    <td style="padding:10px 12px;text-align:center;font-size:13px;color:#555;">{{ number_format($alerta['promedio_diario'], 2, ',', '.') }}</td>
                    <td style="padding:10px 12px;text-align:center;font-size:13px;font-weight:700;color:#b9770e;">{{ number_format($alerta['dias_restantes'], 1, ',', '.') }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>

              <table width="100%" cellpadding="0" cellspacing="0" style="background:#fef9e7;border:1px solid #f9e3a0;border-radius:8px;margin-bottom:24px;">
                <tr>
                  <td style="padding:16px 20px;">
                    <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#856404;">Acciones recomendadas</p>
                    <ul style="margin:0;padding-left:18px;font-size:13px;color:#665000;line-height:1.8;">
                      <li>Priorizar reposición de los productos con menos días estimados.</li>
                      <li>Revisar pedidos a proveedores antes de abrir el local.</li>
                      <li>Ajustar stock mínimo si el patrón real de consumo cambió.</li>
                    </ul>
                  </td>
                </tr>
              </table>

              <p style="margin:0;font-size:12px;color:#999;line-height:1.6;border-top:1px solid #eee;padding-top:16px;">
                Este correo fue generado automáticamente por el sistema <strong>PVenta</strong> como reporte diario predictivo de abastecimiento.
                No responda este mensaje.
              </p>
            </td>
          </tr>

          <tr>
            <td style="background:#f8f9fa;padding:16px 32px;text-align:center;border-top:1px solid #e9ecef;">
              <p style="margin:0;font-size:12px;color:#aaa;">
                <strong style="color:#b9770e;">PVenta</strong> — Sistema de Punto de Venta &nbsp;|&nbsp; {{ $fechaReporte }}
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

</body>
</html>