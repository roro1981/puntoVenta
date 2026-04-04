<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Globales;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VendedorExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $desde;
    protected $hasta;
    protected $vendedorId;

    public function __construct(string $desde, string $hasta, ?int $vendedorId = null)
    {
        $this->desde      = $desde;
        $this->hasta      = $hasta;
        $this->vendedorId = $vendedorId;
    }

    public function collection()
    {
        $desde = Carbon::parse($this->desde)->startOfDay();
        $hasta = Carbon::parse($this->hasta)->endOfDay();

        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        $tipoCaja    = $tipoNegocio === 'RESTAURANT' ? 'RESTAURANT' : 'ALMACEN';

        $rows = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->when($this->vendedorId, fn ($q) => $q->where('v.user_id', $this->vendedorId))
            ->selectRaw("
                v.id as folio,
                DATE_FORMAT(v.fecha_venta, '%d-%m-%Y %H:%i') as fecha_venta,
                COALESCE(u.name_complete, u.name) as vendedor,
                v.forma_pago,
                v.total,
                v.estado
            ")
            ->orderBy('u.name')
            ->orderBy('v.fecha_venta')
            ->get();

        return $rows->map(fn ($r) => [
            'Folio'        => $r->folio,
            'Fecha Venta'  => $r->fecha_venta,
            'Vendedor'     => $r->vendedor,
            'Forma de Pago'=> str_replace('_', ' ', $r->forma_pago ?? ''),
            'Total'        => (float) $r->total,
            'Estado'       => ucfirst($r->estado),
        ]);
    }

    public function headings(): array
    {
        return ['Folio', 'Fecha Venta', 'Vendedor', 'Forma de Pago', 'Total', 'Estado'];
    }

    public function title(): string
    {
        return 'Ventas x Vendedor ' . Carbon::parse($this->desde)->format('d-m-Y') . ' al ' . Carbon::parse($this->hasta)->format('d-m-Y');
    }
}
