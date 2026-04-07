<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\Producto;
use App\Models\HistorialMovimientos;

class ReportesService
{
    public function traeMovimientos($idProducto, $tipoMov, $fecDesde, $fecHasta): string
    {
        $producto = Producto::where('uuid', $idProducto)->first();

        if (!$producto) {
            return '<tr><td colspan="6" class="text-center">Producto no encontrado.</td></tr>';
        }

        $idProd = $producto->id;

        $desde = $fecDesde . ' 00:00:00';
        $hasta = $fecHasta . ' 23:59:59';

        $tipos = [
            2 => 'VENTA',
            3 => 'ENTRADA',
            4 => 'SALIDA',
            5 => 'MERMA',
            6 => 'FACTURA COMPRA',
            7 => 'BOLETA COMPRA',
            8 => 'ANULACIÓN',
        ];

        $movi = $tipos[$tipoMov] ?? null;

        if ($tipoMov == 1) {
            $movimientos = HistorialMovimientos::with('producto')
                ->where('producto_id', $idProd)
                ->whereBetween('fecha', [$desde, $hasta])
                ->orderBy('fecha', 'desc')
                ->get();
        } elseif ($tipoMov == 2) {
            $movimientos = HistorialMovimientos::with('producto')
                ->where('producto_id', $idProd)
                ->whereBetween('fecha', [$desde, $hasta])
                ->where('tipo_mov', $movi)
                ->orderBy('fecha', 'desc')
                ->get();
        } else {
            $movimientos = HistorialMovimientos::with('producto')
                ->where('producto_id', $idProd)
                ->whereBetween('fecha', [$desde, $hasta])
                ->where('tipo_mov', 'like', "%$movi%")
                ->orderBy('fecha', 'desc')
                ->get();
        }

        $filas = '';

        foreach ($movimientos as $pro) {
            $signo = '';
            switch ($pro->tipo_mov) {
                case 'VENTA':
                case 'VENTA (RECETA)':
                case 'VENTA (PROMO)':
                    $signo = ' (-)';
                    $obs = 'TICKET ' . $pro->num_doc;
                    break;
                case 'ENTRADA':
                    $signo = ' (+)';
                    $obs = $pro->obs;
                    break;
                case 'SALIDA':
                case 'MERMA':
                    $signo = ' (-)';
                    $obs = $pro->obs;
                    break;
                case 'FACTURA COMPRA':
                    $signo = ' (+)';
                    $obs = 'FACTURA ' . $pro->num_doc;
                    break;
                case 'BOLETA COMPRA':
                    $signo = ' (+)';
                    $obs = 'BOLETA ' . $pro->num_doc;
                    break;
                case 'ANULACIÓN':
                    $signo = ' (+)';
                    $obs = $pro->obs;
                    break;    
                default:
                    $obs = '';
            }

            $filas .= '<tr><td style="text-align:center">' . Carbon::parse($pro->fecha)->format('d-m-Y H:i:s') . '</td>' .
                      '<td>' . $pro->producto->descripcion . '</td>' .
                      '<td style="text-align:center">' . $pro->tipo_mov . $signo . '</td>' .
                      '<td style="text-align:center">' . $pro->cantidad . '</td>' .
                      '<td style="text-align:center">' . $pro->stock . '</td>' .
                      '<td style="text-align:center">' . $obs . '</td></tr>';
        }

        return $filas;
    }
    public function nombreTipoMovimiento(int $codigo): string
    {
        return match ($codigo) {
            1 => 'TODOS',
            2 => 'VENTA',
            3 => 'ENTRADA',
            4 => 'SALIDA',
            5 => 'MERMA',
            6 => 'FACTURA COMPRA',
            7 => 'BOLETA COMPRA',
            default => 'Desconocido',
        };
    }

    public function traerMovimientos($tipoMov, $uuidProducto, $desde, $hasta)
    {
        $producto = Producto::where('uuid', $uuidProducto)->firstOrFail();
        $idProducto = $producto->id;

        $query = HistorialMovimientos::with('producto')
            ->where('producto_id', $idProducto)
            ->whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);

        if ($tipoMov != '1') {
            $mapeo = [
                '2' => 'VENTA',
                '3' => 'ENTRADA',
                '4' => 'SALIDA',
                '5' => 'MERMA',
                '6' => 'FACTURA COMPRA',
                '7' => 'BOLETA COMPRA',
                '8' => 'ANULACIÓN',
            ];
            $tipoTexto = $mapeo[$tipoMov] ?? null;

            if ($tipoMov == '2') {
                $query->where('tipo_mov', 'like', "%$tipoTexto%");
            } elseif ($tipoTexto) {
                $query->where('tipo_mov', $tipoTexto);
            }
        }
        return $query->orderBy('fecha', 'asc')->get();
    }

    /**
     * Devuelve movimientos como array para respuesta JSON.
     */
    public function dataMovimientosJson(string $uuid, int $tipoMov, string $desde, string $hasta): array
    {
        $producto = Producto::where('uuid', $uuid)->first();
        if (!$producto) {
            return ['movimientos' => [], 'nombre' => '', 'stock_actual' => 0];
        }

        $registros = $this->traerMovimientos((string) $tipoMov, $uuid, $desde, $hasta);

        $esEntrada = fn(string $t) => in_array($t, ['ENTRADA', 'FACTURA COMPRA', 'BOLETA COMPRA']);
        $esSalida  = fn(string $t) => in_array($t, ['SALIDA', 'MERMA', 'VENTA', 'VENTA (RECETA)', 'VENTA (PROMO)']);

        $totalEntradas = 0;
        $totalSalidas  = 0;

        $movimientos = $registros->map(function ($m) use ($esEntrada, $esSalida, &$totalEntradas, &$totalSalidas) {
            $tipo = $m->tipo_mov;
            if ($esEntrada($tipo)) {
                $signo = '+';
                $totalEntradas += abs((float) $m->cantidad);
            } elseif ($esSalida($tipo)) {
                $signo = '-';
                $totalSalidas += abs((float) $m->cantidad);
            } else {
                $signo = '';
            }

            $numDoc  = $m->num_doc ?? '';
            $obsRaw  = trim((string) ($m->obs ?? ''));
            $sinObs  = $obsRaw === '' || $obsRaw === '-';
            $obs = match (true) {
                in_array($tipo, ['VENTA', 'VENTA (RECETA)', 'VENTA (PROMO)']) => $sinObs ? ('TICKET '  . $numDoc) : $obsRaw,
                in_array($tipo, ['FACTURA COMPRA']) => $sinObs ? ('FACTURA ' . $numDoc) : $obsRaw,
                in_array($tipo, ['BOLETA COMPRA'])  => $sinObs ? ('BOLETA '  . $numDoc) : $obsRaw,
                $tipo === 'ENTRADA'   => $sinObs ? ($numDoc ? 'ENTRADA N° ' . $numDoc : 'ENTRADA')  : $obsRaw,
                $tipo === 'SALIDA'    => $sinObs ? ($numDoc ? 'SALIDA N° '  . $numDoc : 'SALIDA')   : $obsRaw,
                $tipo === 'MERMA'     => $sinObs ? ($numDoc ? 'MERMA N° '   . $numDoc : 'MERMA')    : $obsRaw,
                $tipo === 'ANULACIÓN' => $sinObs ? ($numDoc ? 'ANULACIÓN N° '. $numDoc : 'ANULACIÓN') : $obsRaw,
                default => $obsRaw,
            };

            return [
                'fecha'    => Carbon::parse($m->fecha)->format('d-m-Y H:i'),
                'tipo_mov' => $tipo,
                'signo'    => $signo,
                'cantidad' => abs((float) $m->cantidad),
                'stock'    => (float) $m->stock,
                'obs'      => $obs,
            ];
        })->values()->all();

        return [
            'nombre'         => $producto->descripcion,
            'codigo'         => $producto->codigo ?? '',
            'stock_actual'   => (float) $producto->stock,
            'total_entradas' => $totalEntradas,
            'total_salidas'  => $totalSalidas,
            'variacion_neta' => $totalEntradas - $totalSalidas,
            'movimientos'    => array_merge(
                [[
                    'fecha'    => Carbon::parse($producto->fec_creacion ?? $producto->created_at)->format('d-m-Y H:i'),
                    'tipo_mov' => 'CREACIÓN',
                    'signo'    => '',
                    'cantidad' => 0,
                    'stock'    => 0,
                    'obs'      => 'Alta del producto en el sistema',
                ]],
                $movimientos
            ),
        ];
    }

    /**
     * Búsqueda de productos para autocomplete.
     */
    public function buscarProductos(string $q): array
    {
        return Producto::where('estado', 'Activo')
            ->where('tipo', '<>', 'S')
            ->where(function ($query) use ($q) {
                $query->where('descripcion', 'like', "%{$q}%")
                      ->orWhere('codigo', 'like', "%{$q}%");
            })
            ->orderBy('descripcion')
            ->limit(12)
            ->get(['id', 'uuid', 'codigo', 'descripcion', 'stock'])
            ->map(fn ($p) => [
                'uuid'        => $p->uuid,
                'codigo'      => $p->codigo ?? '',
                'descripcion' => $p->descripcion,
                'stock'       => (float) $p->stock,
            ])->all();
    }
}