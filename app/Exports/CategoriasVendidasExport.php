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

class CategoriasVendidasExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $desde;
    protected $hasta;

    public function __construct($desde, $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection(): Collection
    {
        $desde       = Carbon::parse($this->desde)->startOfDay();
        $hasta       = Carbon::parse($this->hasta)->endOfDay();
        $tipoNegocio = strtoupper(trim(
            (string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')
        ));

        $rows       = $this->getRankingCategorias($desde, $hasta, $tipoNegocio);
        $totalMonto = $rows->sum(fn ($r) => (float) $r->monto);

        $result = [];
        foreach ($rows as $i => $r) {
            $monto      = (float) $r->monto;
            $unidades   = (float) $r->unidades;
            $particip   = $totalMonto > 0 ? round($monto / $totalMonto * 100, 1) : 0;

            $result[] = [
                $i + 1,
                $r->categoria,
                $unidades,
                $monto,
                $particip . '%',
            ];
        }

        return collect($result);
    }

    private function getRankingCategorias(Carbon $desde, Carbon $hasta, string $tipoNegocio)
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
                ->selectRaw("
                    COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria,
                    SUM(dc.subtotal) as monto,
                    SUM(dc.cantidad) as unidades
                ")
                ->groupBy(DB::raw("COALESCE(cat.descripcion_categoria, 'Sin categoría')"))
                ->orderByDesc(DB::raw('SUM(dc.subtotal)'))
                ->get();
        } else {
            $fromProductos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNull('dv.promo_id')
                ->whereNotNull('dv.producto_uuid')
                ->selectRaw("
                    COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria,
                    SUM(dv.subtotal_linea) as monto,
                    SUM(dv.cantidad) as unidades
                ")
                ->groupBy(DB::raw("COALESCE(cat.descripcion_categoria, 'Sin categoría')"))
                ->get();

            $fromPromos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->join('promociones as promo', 'promo.id', '=', 'dv.promo_id')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'promo.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNotNull('dv.promo_id')
                ->selectRaw("
                    COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria,
                    SUM(dv.subtotal_linea) as monto,
                    SUM(dv.cantidad) as unidades
                ")
                ->groupBy(DB::raw("COALESCE(cat.descripcion_categoria, 'Sin categoría')"))
                ->get();

            return $fromProductos->concat($fromPromos)
                ->groupBy('categoria')
                ->map(function ($rows, $cat) {
                    return (object) [
                        'categoria' => $cat,
                        'monto'     => $rows->sum(fn ($r) => (float) $r->monto),
                        'unidades'  => $rows->sum(fn ($r) => (float) $r->unidades),
                    ];
                })
                ->sortByDesc(fn ($r) => $r->monto)
                ->values();
        }
    }

    public function headings(): array
    {
        return ['#', 'Categoría', 'Unidades', 'Ingresos ($)', 'Participación'];
    }

    public function title(): string
    {
        return 'Categorías más vendidas';
    }
}
