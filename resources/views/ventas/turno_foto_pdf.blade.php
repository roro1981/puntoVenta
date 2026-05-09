<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Foto del Turno</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #1f2937; }
        .page { padding: 28px; }
        .header { background: #0f766e; color: #fff; padding: 18px 20px; border-radius: 10px; }
        .header h1 { margin: 0 0 6px 0; font-size: 22px; }
        .header p { margin: 0; font-size: 12px; }
        .grid { margin-top: 14px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 10px; }
        .title { color: #0f766e; font-size: 12px; text-transform: uppercase; margin-bottom: 6px; }
        .value { font-size: 22px; font-weight: 700; margin: 0; }
        .small { font-size: 12px; color: #6b7280; margin-top: 3px; }
        .row { width: 100%; border-collapse: collapse; }
        .row td { width: 50%; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { border-bottom: 1px solid #e5e7eb; padding: 7px 6px; font-size: 12px; }
        .table th { text-align: left; background: #f9fafb; }
        .right { text-align: right; }
        .footer { margin-top: 12px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>Foto del Turno - {{ $modulo_origen }}</h1>
        <p>{{ $empresa }} | Caja #{{ $caja->id }} | Apertura: {{ $fecha_apertura->format('d/m/Y H:i') }} | Cierre: {{ $fecha_cierre->format('d/m/Y H:i') }}</p>
    </div>

    <table class="row grid">
        <tr>
            <td style="padding-right: 6px;">
                <div class="card">
                    <div class="title">Total vendido</div>
                    <p class="value">${{ number_format($total_ventas, 0, ',', '.') }}</p>
                    <div class="small">{{ $cantidad_ventas }} venta(s)</div>
                </div>
            </td>
            <td style="padding-left: 6px;">
                <div class="card">
                    <div class="title">Comparativa vs ayer</div>
                    @if(is_null($variacion_vs_ayer))
                        <p class="value">N/A</p>
                        <div class="small">Ayer: ${{ number_format($total_ayer, 0, ',', '.') }}</div>
                    @else
                        <p class="value">{{ $variacion_vs_ayer >= 0 ? '+' : '' }}{{ number_format($variacion_vs_ayer, 1, ',', '.') }}%</p>
                        <div class="small">Ayer: ${{ number_format($total_ayer, 0, ',', '.') }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="row">
        <tr>
            <td style="padding-right: 6px;">
                <div class="card">
                    <div class="title">Producto estrella del turno</div>
                    <p class="value" style="font-size:18px;">{{ $producto_estrella['nombre'] }}</p>
                    <div class="small">Cantidad: {{ number_format($producto_estrella['cantidad'], 2, ',', '.') }}</div>
                </div>
            </td>
            <td style="padding-left: 6px;">
                <div class="card">
                    <div class="title">Forma de pago dominante</div>
                    <p class="value" style="font-size:18px;">{{ $forma_dominante['forma'] }}</p>
                    <div class="small">Monto: ${{ number_format($forma_dominante['monto'], 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="card" style="margin-top:10px;">
        <div class="title">Desglose por forma de pago</div>
        <table class="table">
            <thead>
            <tr>
                <th>Forma de pago</th>
                <th class="right">Monto</th>
            </tr>
            </thead>
            <tbody>
            @foreach($desglose_formas as $forma => $monto)
                <tr>
                    <td>{{ str_replace('_', ' ', $forma) }}</td>
                    <td class="right">${{ number_format($monto, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Diferencia de caja: ${{ number_format($diferencia, 0, ',', '.') }} | Generado automaticamente por PVenta.
    </div>
</div>
</body>
</html>