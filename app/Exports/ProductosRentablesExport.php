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

class ProductosRentablesExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $desde;
    protected $hasta;
    protected $categoriaId;

    public function __construct($desde, $hasta, $categoriaId = null)
    {
        $this->desde      = $desde;
        $this->hasta      = $hasta;
        $this->categoriaId = $categoriaId ? (int) $categoriaId : null;
    }

    public function collection(): Collection
    {
        $desde       = Carbon::parse($this->desde)->startOfDay();
        $hasta       = Carbon::parse($this->hasta)->endOfDay();
        $tipoNegocio = strtoupper(trim(
            (string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')
        ));

        $rows = $this->getRankingData($desde, $hasta, $tipoNegocio, $this->categoriaId);

        $result = [];
        foreach ($rows as $i => $r) {
            $ingresos  = (float) $r->ingresos;
            $costo     = (float) $r->costo_total;
            $utilidad  = $ingresos - $costo;
            $margen    = $ingresos > 0 ? round($utilidad / $ingresos * 100, 1) : 0;

            if ($margen >= 40) {
                $estado = 'Excelente';
            } elseif ($margen >= 20) {
                $estado = 'Bueno';
            } elseif ($margen >= 5) {
                $estado = 'Bajo';
            } else {
                $estado = 'Crítico';
            }

            $result[] = [
                $i + 1,
                $r->nombre,
                $r->codigo ?? '',
                $r->categoria ?? 'Sin categoría',
                (float) $r->unidades,
                $ingresos,
                $costo,
                $utilidad,
                $margen . '%',
                $estado,
            ];
        }

        return collect($result);
    }

    private function getRankingData(Carbon $desde, Carbon $hasta, string $tipoNegocio, ?int $categoriaId)
    {
        if ($tipoNegocio === 'RESTAURANT') {
            return DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->leftJoin('productos as p', 'p.id', '=', 'dc.producto_id')
                ->leftJoin('recetas as r', 'r.id', '=', 'dc.receta_id')
                ->leftJoin('categorias as cat', function ($join) {
                    $join->on('cat.id', '=', DB::raw('COALESCE(p.categoria_id, r.categoria_id)'));
                })
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$desde, $hasta])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->when($categoriaId, fn ($q) => $q->whereRaw('COALESCE(p.categoria_id, r.categoria_id) = ?', [$categoriaId]))
                ->selectRaw("
                    dc.producto_id,
                    dc.receta_id,
                    MAX(COALESCE(p.descripcion, r.nombre, 'Sin nombre')) as nombre,
                    MAX(COALESCE(p.codigo, r.codigo, '')) as codigo,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    SUM(dc.cantidad) as unidades,
                    SUM(dc.subtotal) as ingresos,
                    SUM(dc.cantidad * COALESCE(p.precio_compra_neto, r.precio_costo, 0)) as costo_total
                ")
                ->groupBy('dc.producto_id', 'dc.receta_id')
                ->orderByDesc(DB::raw('SUM(dc.subtotal) - SUM(dc.cantidad * COALESCE(p.precio_compra_neto, r.precio_costo, 0))'))
                ->get();
        } else {
            return DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->when($categoriaId, fn ($q) => $q->where('p.categoria_id', $categoriaId))
                ->selectRaw("
                    COALESCE(dv.producto_uuid, dv.descripcion_producto) as prod_key,
                    MAX(COALESCE(p.descripcion, dv.descripcion_producto, 'Sin nombre')) as nombre,
                    MAX(COALESCE(p.codigo, '')) as codigo,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    SUM(dv.cantidad) as unidades,
                    SUM(dv.subtotal_linea) as ingresos,
                    SUM(dv.cantidad * COALESCE(p.precio_compra_neto, 0)) as costo_total
                ")
                ->groupBy(DB::raw('COALESCE(dv.producto_uuid, dv.descripcion_producto)'))
                ->orderByDesc(DB::raw('SUM(dv.subtotal_linea) - SUM(dv.cantidad * COALESCE(p.precio_compra_neto, 0))'))
                ->get();
        }
    }

    public function headings(): array
    {
        return [
            'Ranking', 'Producto', 'Código/SKU', 'Categoría',
            'Unidades', 'Ingresos ($)', 'Costo ($)', 'Utilidad ($)', 'Margen %', 'Estado',
        ];
    }

    public function title(): string
    {
        return 'Productos más rentables';
    }
}
