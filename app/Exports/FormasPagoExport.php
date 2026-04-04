<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Globales;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FormasPagoExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
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

        // Obtener la forma de pago dominante (mayor monto) para ordenar primero por ella
        $dominante = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->leftJoin('formas_pago_venta as fpv', 'fpv.venta_id', '=', 'v.id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw("
                CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.forma_pago ELSE v.forma_pago END as forma,
                SUM(CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.monto ELSE v.total END) as monto
            ")
            ->groupBy(DB::raw("CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.forma_pago ELSE v.forma_pago END"))
            ->havingRaw("forma IS NOT NULL AND forma != ''")
            ->orderByDesc('monto')
            ->value('forma');

        // Detalle de ventas: las MIXTO se expanden en una fila por forma de pago
        $rows = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->leftJoin('users as u', 'u.id', '=', 'v.user_id')
            ->leftJoin('formas_pago_venta as fpv', 'fpv.venta_id', '=', 'v.id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw("
                v.id as folio,
                DATE_FORMAT(v.fecha_venta, '%d-%m-%Y %H:%i') as fecha_venta,
                COALESCE(u.name_complete, u.name, 'Sin usuario') as cajero,
                CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.forma_pago ELSE v.forma_pago END as forma_pago,
                CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.monto ELSE v.total END as monto,
                v.total as total_venta
            ")
            ->havingRaw("forma_pago IS NOT NULL AND forma_pago != ''")
            ->orderByRaw("CASE WHEN (CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.forma_pago ELSE v.forma_pago END) = ? THEN 0 ELSE 1 END", [$dominante ?? ''])
            ->orderBy(DB::raw("CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.forma_pago ELSE v.forma_pago END"))
            ->orderBy('v.fecha_venta')
            ->get();

        return $rows->map(fn ($r) => [
            'Folio'        => $r->folio,
            'Fecha Venta'  => $r->fecha_venta,
            'Cajero'       => $r->cajero,
            'Forma de Pago'=> str_replace('_', ' ', $r->forma_pago),
            'Monto'        => (float) $r->monto,
            'Total Venta'  => (float) $r->total_venta,
        ]);
    }

    public function headings(): array
    {
        return ['Folio', 'Fecha Venta', 'Cajero', 'Forma de Pago', 'Monto', 'Total Venta'];
    }

    public function title(): string
    {
        return 'Formas de Pago ' . Carbon::parse($this->desde)->format('d-m-Y') . ' al ' . Carbon::parse($this->hasta)->format('d-m-Y');
    }
}
