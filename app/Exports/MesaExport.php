<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MesaExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $desde;
    protected $hasta;

    public function __construct($desde, $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection()
    {
        $desde = Carbon::parse($this->desde)->startOfDay();
        $hasta = Carbon::parse($this->hasta)->endOfDay();

        return DB::table('comandas as com')
            ->leftJoin('mesas as m', 'm.id', '=', 'com.mesa_id')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->selectRaw("
                com.numero_comanda as Folio,
                DATE_FORMAT(com.fecha_cierre, '%d-%m-%Y %H:%i') as 'Fecha Cierre',
                COALESCE(m.nombre, 'Sin mesa') as Mesa,
                com.comensales as Comensales,
                com.subtotal as Subtotal,
                CASE WHEN com.incluye_propina = 1 THEN com.propina ELSE 0 END as Propina,
                com.total as Total
            ")
            ->orderBy('m.nombre')
            ->orderBy('com.fecha_cierre')
            ->get();
    }

    public function headings(): array
    {
        return ['Folio', 'Fecha Cierre', 'Mesa', 'Comensales', 'Subtotal', 'Propina', 'Total'];
    }

    public function title(): string
    {
        return 'Ventas por Mesa';
    }
}
