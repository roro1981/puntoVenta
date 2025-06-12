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
        ];

        $movi = $tipos[$tipoMov] ?? null;

        if ($tipoMov == 1) {
            $movimientos = HistorialMovimientos::with('producto')
                ->where('producto_id', $idProd)
                ->whereBetween('fecha', [$desde, $hasta])
                ->orderBy('fecha')
                ->get();
        } elseif ($tipoMov == 2) {
            $movimientos = HistorialMovimientos::with('producto')
                ->where('producto_id', $idProd)
                ->whereBetween('fecha', [$desde, $hasta])
                ->where('tipo_mov', $movi)
                ->orderBy('fecha')
                ->get();
        } else {
            $movimientos = HistorialMovimientos::with('producto')
                ->where('producto_id', $idProd)
                ->whereBetween('fecha', [$desde, $hasta])
                ->where('tipo_mov', 'like', "%$movi%")
                ->orderBy('fecha')
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
}