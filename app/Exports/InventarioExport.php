<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Globales;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InventarioExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function collection(): Collection
    {
        $tipoNegocio = strtoupper(trim(
            (string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')
        ));

        $query = DB::table('productos as p')
            ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
            ->where('p.estado', 'Activo')
            ->whereNull('p.fec_eliminacion');

        if ($tipoNegocio !== 'RESTAURANT') {
            $query->where('p.tipo', '<>', 'I');
        }

        $productos = $query->selectRaw("
            p.id, p.uuid, p.codigo, p.descripcion, p.tipo, p.unidad_medida,
            p.stock, p.stock_minimo, p.precio_compra_neto, p.precio_venta,
            COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria,
            (p.stock * p.precio_compra_neto) as valor_inventario
        ")->get();

        // Ventas últimos 30 días
        $hace30 = Carbon::now()->subDays(30)->startOfDay();
        $ahora  = Carbon::now()->endOfDay();

        if ($tipoNegocio === 'RESTAURANT') {
            $ventas30 = DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$hace30, $ahora])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->whereNotNull('dc.producto_id')
                ->selectRaw('dc.producto_id, SUM(dc.cantidad) as vendido')
                ->groupBy('dc.producto_id')
                ->get()->keyBy('producto_id');
        } else {
            $ventas30 = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$hace30, $ahora])
                ->whereNull('dv.promo_id')
                ->whereNotNull('dv.producto_uuid')
                ->selectRaw('dv.producto_uuid, SUM(dv.cantidad) as vendido')
                ->groupBy('dv.producto_uuid')
                ->get()->keyBy('producto_uuid');
        }

        $result = [];
        foreach ($productos as $i => $p) {
            $stock    = (float) $p->stock;
            $stockMin = (float) $p->stock_minimo;

            $v30Row    = $tipoNegocio === 'RESTAURANT'
                ? ($ventas30[$p->id]   ?? null)
                : ($ventas30[$p->uuid] ?? null);
            $vendido30 = $v30Row ? (float) $v30Row->vendido : 0;

            $diasCobertura = ($vendido30 > 0 && $stock > 0)
                ? (int) round($stock / ($vendido30 / 30))
                : null;

            if ($stock <= 0) {
                $estado = 'Agotado';
            } elseif ($stock <= $stockMin) {
                $estado = 'Crítico';
            } elseif ($stockMin > 0 && $stock > $stockMin * 5 && $vendido30 == 0) {
                $estado = 'Sobrestock';
            } else {
                $estado = 'Normal';
            }

            $result[] = [
                $i + 1,
                $p->codigo,
                $p->descripcion,
                $p->categoria,
                $p->tipo,
                $p->unidad_medida,
                $stock,
                $stockMin,
                (float) $p->precio_compra_neto,
                (float) $p->precio_venta,
                round((float) $p->valor_inventario),
                $vendido30,
                $diasCobertura ?? 'Sin rotación',
                $estado,
            ];
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            '#', 'Código', 'Producto', 'Categoría', 'Tipo', 'Unidad',
            'Stock actual', 'Stock mínimo', 'Precio costo', 'Precio venta',
            'Valor inventario ($)', 'Ventas 30d', 'Días cobertura', 'Estado',
        ];
    }

    public function title(): string
    {
        return 'Inventario';
    }
}
