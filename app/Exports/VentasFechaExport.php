<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Globales;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentasFechaExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $desde;
    protected $hasta;

    public function __construct(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection()
    {
        $desde = Carbon::parse($this->desde)->startOfDay();
        $hasta = Carbon::parse($this->hasta)->endOfDay();

        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        $tipoCaja    = $tipoNegocio === 'RESTAURANT' ? 'RESTAURANT' : 'ALMACEN';

        if ($tipoNegocio === 'RESTAURANT') {
            $rows = DB::table('comandas as c')
                ->leftJoin('garzon as g', 'g.id', '=', 'c.garzon_id')
                ->leftJoin('mesas as m', 'm.id', '=', 'c.mesa_id')
                ->where('c.estado', 'CERRADA')
                ->whereBetween('c.fecha_cierre', [$desde, $hasta])
                ->selectRaw("
                    c.id as folio,
                    DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i') as fecha_cierre,
                    COALESCE(g.nombre, 'Sin garzón') as garzon,
                    COALESCE(m.nombre, 'Sin mesa') as mesa,
                    c.total,
                    c.estado
                ")
                ->orderBy('c.fecha_cierre')
                ->get();

            return $rows->map(fn ($r) => [
                'Folio'        => $r->folio,
                'Fecha Cierre' => $r->fecha_cierre,
                'Garzón'       => $r->garzon,
                'Mesa'         => $r->mesa,
                'Total'        => (float) $r->total,
                'Estado'       => $r->estado,
            ]);
        }

        // ALMACEN
        $rows = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->leftJoin('users as u', 'u.id', '=', 'v.user_id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw("
                v.id as folio,
                DATE_FORMAT(v.fecha_venta, '%d-%m-%Y %H:%i') as fecha_venta,
                COALESCE(u.name_complete, u.name, 'Sin usuario') as cajero,
                v.forma_pago,
                v.total,
                v.estado,
                ca.tipo_caja
            ")
            ->orderBy('v.fecha_venta')
            ->get();

        return $rows->map(fn ($r) => [
            'Folio'       => $r->folio,
            'Fecha Venta' => $r->fecha_venta,
            'Cajero'      => $r->cajero,
            'Forma Pago'  => str_replace('_', ' ', $r->forma_pago),
            'Total'       => (float) $r->total,
            'Estado'      => $r->estado,
        ]);
    }

    public function headings(): array
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));

        if ($tipoNegocio === 'RESTAURANT') {
            return ['Folio', 'Fecha Cierre', 'Garzón', 'Mesa', 'Total', 'Estado'];
        }

        return ['Folio', 'Fecha Venta', 'Cajero', 'Forma Pago', 'Total', 'Estado'];
    }

    public function title(): string
    {
        return 'Ventas ' . Carbon::parse($this->desde)->format('d-m-Y') . ' al ' . Carbon::parse($this->hasta)->format('d-m-Y');
    }
}
