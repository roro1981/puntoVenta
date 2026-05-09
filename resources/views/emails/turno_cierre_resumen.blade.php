<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Cierre de Caja</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:20px 0;">
    <tr>
        <td align="center">
            <table width="760" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;">
                <tr>
                    <td style="background:#0f766e;padding:22px 26px;color:#ffffff;">
                        <h1 style="margin:0;font-size:24px;">Cierre de Caja - {{ $data['cajero_nombre'] }}</h1>
                        <p style="margin:6px 0 0 0;font-size:13px;opacity:.92;">
                            {{ $data['empresa'] }} | Caja #{{ $data['caja']->id }} | {{ $data['modulo_origen'] }}
                        </p>
                        <p style="margin:4px 0 0 0;font-size:12px;opacity:.85;">
                            Inicio: {{ $data['fecha_apertura']->format('d/m/Y H:i:s') }} | Cierre: {{ $data['fecha_cierre']->format('d/m/Y H:i:s') }} | Duración: {{ $data['duracion'] }}
                        </p>
                        @if(!empty($data['observaciones']))
                        <p style="margin:8px 0 0 0;font-size:12px;color:#fbbf24;background-color:rgba(251,191,36,0.1);padding:8px;border-radius:4px;white-space:pre-line;">
                            <strong>Observaciones:</strong> {!! e($data['observaciones']) !!}
                        </p>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="padding:22px 26px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:14px;">
                            <tr>
                                <td width="50%" style="padding:6px;vertical-align:top;">
                                    <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;">
                                        <div style="font-size:12px;color:#0f766e;text-transform:uppercase;">Total Vendido</div>
                                        <div style="font-size:28px;font-weight:700;">${{ number_format($data['total_ventas'], 0, ',', '.') }}</div>
                                        <div style="font-size:12px;color:#6b7280;">{{ $data['cantidad_ventas'] }} venta(s)</div>
                                    </div>
                                </td>
                                <td width="50%" style="padding:6px;vertical-align:top;">
                                    <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;">
                                        <div style="font-size:12px;color:#0f766e;text-transform:uppercase;">Comparativa vs Ayer</div>
                                        @if(is_null($data['variacion_vs_ayer']))
                                            <div style="font-size:28px;font-weight:700;">N/A</div>
                                            <div style="font-size:12px;color:#6b7280;">Ayer: ${{ number_format($data['total_ayer'], 0, ',', '.') }}</div>
                                        @else
                                            <div style="font-size:28px;font-weight:700;">{{ $data['variacion_vs_ayer'] >= 0 ? '+' : '' }}{{ number_format($data['variacion_vs_ayer'], 1, ',', '.') }}%</div>
                                            <div style="font-size:12px;color:#6b7280;">Ayer: ${{ number_format($data['total_ayer'], 0, ',', '.') }}</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:14px;">
                            <tr>
                                <td width="50%" style="padding:6px;vertical-align:top;">
                                    <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;">
                                        <div style="font-size:12px;color:#0f766e;text-transform:uppercase;">Producto Estrella</div>
                                        <div style="font-size:17px;font-weight:700;">{{ $data['producto_estrella']['nombre'] }}</div>
                                        <div style="font-size:12px;color:#6b7280;">Cantidad: {{ number_format($data['producto_estrella']['cantidad'], 2, ',', '.') }}</div>
                                    </div>
                                </td>
                                <td width="50%" style="padding:6px;vertical-align:top;">
                                    <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;">
                                        <div style="font-size:12px;color:#0f766e;text-transform:uppercase;">Forma de Pago Dominante</div>
                                        <div style="font-size:17px;font-weight:700;">{{ $data['forma_dominante']['forma'] }}</div>
                                        <div style="font-size:12px;color:#6b7280;">Monto: ${{ number_format($data['forma_dominante']['monto'], 0, ',', '.') }}</div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;margin-bottom:14px;">
                            <div style="font-size:12px;color:#0f766e;text-transform:uppercase;margin-bottom:8px;">Desglose por Forma de Pago</div>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Forma</th>
                                        <th style="text-align:right;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['desglose_formas'] as $forma => $monto)
                                        <tr>
                                            <td style="padding:7px;border-bottom:1px solid #f1f5f9;font-size:12px;">{{ str_replace('_', ' ', $forma) }}</td>
                                            <td style="padding:7px;border-bottom:1px solid #f1f5f9;text-align:right;font-size:12px;">${{ number_format($monto, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;">
                            <div style="font-size:12px;color:#0f766e;text-transform:uppercase;margin-bottom:8px;">Detalle de Productos Vendidos (de mayor a menor)</div>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">#</th>
                                        <th style="text-align:left;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Producto</th>
                                        <th style="text-align:right;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Cantidad</th>
                                        <th style="text-align:right;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data['productos_resumen'] as $item)
                                        <tr>
                                            <td style="padding:7px;border-bottom:1px solid #f1f5f9;font-size:12px;">{{ $loop->iteration }}</td>
                                            <td style="padding:7px;border-bottom:1px solid #f1f5f9;font-size:12px;">{{ $item['producto'] }}</td>
                                            <td style="padding:7px;border-bottom:1px solid #f1f5f9;text-align:right;font-size:12px;">{{ number_format($item['cantidad'], 2, ',', '.') }}</td>
                                            <td style="padding:7px;border-bottom:1px solid #f1f5f9;text-align:right;font-size:12px;">${{ number_format($item['monto'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="padding:10px;text-align:center;color:#6b7280;font-size:12px;">Sin productos vendidos en este cierre.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(!empty($data['es_restaurant']) && !empty($data['restaurant']))
                        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;margin-top:14px;">
                            <div style="font-size:12px;color:#0f766e;text-transform:uppercase;margin-bottom:8px;">Indicadores RESTAURANT del Turno</div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
                                <tr>
                                    <td width="50%" style="padding:6px;vertical-align:top;">
                                        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                                            <div style="font-size:12px;color:#0f766e;text-transform:uppercase;">Comensales atendidos</div>
                                            <div style="font-size:20px;font-weight:700;">{{ number_format($data['restaurant']['comensales_atendidos'], 0, ',', '.') }}</div>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding:6px;vertical-align:top;">
                                        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                                            <div style="font-size:12px;color:#0f766e;text-transform:uppercase;">Mesas atendidas</div>
                                            <div style="font-size:20px;font-weight:700;">{{ number_format($data['restaurant']['mesas_atendidas'], 0, ',', '.') }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
                                <tr>
                                    <td width="50%" style="padding:6px;vertical-align:top;">
                                        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                                            <div style="font-size:12px;color:#0f766e;text-transform:uppercase;">Mesa mas ocupada</div>
                                            @if(!empty($data['restaurant']['mesa_mas_ocupada']))
                                                <div style="font-size:17px;font-weight:700;">{{ $data['restaurant']['mesa_mas_ocupada']['mesa'] }}</div>
                                                <div style="font-size:12px;color:#6b7280;">Comensales: {{ number_format($data['restaurant']['mesa_mas_ocupada']['comensales'], 0, ',', '.') }} | Comandas: {{ number_format($data['restaurant']['mesa_mas_ocupada']['comandas'], 0, ',', '.') }}</div>
                                            @else
                                                <div style="font-size:17px;font-weight:700;">N/A</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td width="50%" style="padding:6px;vertical-align:top;">
                                        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                                            <div style="font-size:12px;color:#0f766e;text-transform:uppercase;">Propinas del turno</div>
                                            <div style="font-size:20px;font-weight:700;">${{ number_format($data['restaurant']['total_propinas'], 0, ',', '.') }}</div>
                                            <div style="font-size:12px;color:#6b7280;">Ticket por comensal: ${{ number_format($data['restaurant']['ticket_promedio_comensal'], 0, ',', '.') }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="font-size:12px;color:#0f766e;text-transform:uppercase;margin:6px 0 8px 0;">Propinas por garzon</div>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <thead>
                                <tr>
                                    <th style="text-align:left;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">#</th>
                                    <th style="text-align:left;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Garzon</th>
                                    <th style="text-align:right;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Comandas</th>
                                    <th style="text-align:right;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Comensales</th>
                                    <th style="text-align:right;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Propina</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($data['restaurant']['propinas_por_garzon'] as $item)
                                    <tr>
                                        <td style="padding:7px;border-bottom:1px solid #f1f5f9;font-size:12px;">{{ $loop->iteration }}</td>
                                        <td style="padding:7px;border-bottom:1px solid #f1f5f9;font-size:12px;">{{ $item['garzon'] }}</td>
                                        <td style="padding:7px;border-bottom:1px solid #f1f5f9;text-align:right;font-size:12px;">{{ number_format($item['comandas'], 0, ',', '.') }}</td>
                                        <td style="padding:7px;border-bottom:1px solid #f1f5f9;text-align:right;font-size:12px;">{{ number_format($item['comensales'], 0, ',', '.') }}</td>
                                        <td style="padding:7px;border-bottom:1px solid #f1f5f9;text-align:right;font-size:12px;">${{ number_format($item['propina_total'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding:10px;text-align:center;color:#6b7280;font-size:12px;">Sin informacion de propinas por garzon en este turno.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @endif

                        @if(!empty($data['retiros']['detalle']))
                        <div style="margin-top:18px;">
                            <div style="font-size:12px;color:#b45309;text-transform:uppercase;font-weight:700;margin-bottom:8px;">Retiros de caja</div>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <thead>
                                <tr>
                                    <th style="text-align:left;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">#</th>
                                    <th style="text-align:left;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Motivo</th>
                                    <th style="text-align:right;padding:7px;border-bottom:1px solid #e5e7eb;font-size:12px;">Monto</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($data['retiros']['detalle'] as $retiro)
                                    <tr>
                                        <td style="padding:7px;border-bottom:1px solid #f1f5f9;font-size:12px;">{{ $loop->iteration }}</td>
                                        <td style="padding:7px;border-bottom:1px solid #f1f5f9;font-size:12px;">{{ $retiro['motivo'] }}</td>
                                        <td style="padding:7px;border-bottom:1px solid #f1f5f9;text-align:right;font-size:12px;">${{ number_format($retiro['monto'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="2" style="padding:7px;font-size:12px;font-weight:700;">Total retiros ({{ $data['retiros']['cantidad'] }})</td>
                                    <td style="padding:7px;text-align:right;font-size:12px;font-weight:700;color:#b45309;">${{ number_format($data['retiros']['total'], 0, ',', '.') }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        @endif

                        <p style="margin:14px 0 0 0;font-size:11px;color:#6b7280;">
                            Diferencia de caja: ${{ number_format($data['diferencia'], 0, ',', '.') }} | Correo generado automaticamente por PVenta.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>