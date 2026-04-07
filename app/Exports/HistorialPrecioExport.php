<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class HistorialPrecioExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private string $tipo,
        private int    $entidadId
    ) {}

    public function collection(): Collection
    {
        $rows = DB::table('historial_precios')
            ->where('entidad_tipo', $this->tipo)
            ->where('entidad_id', $this->entidadId)
            ->orderBy('fecha_cambio')
            ->get();

        return $rows->map(function ($r, $i) {
            $variacion = null;
            if ($r->precio_anterior !== null && (float) $r->precio_anterior > 0) {
                $variacion = round((((float)$r->precio_nuevo - (float)$r->precio_anterior) / (float)$r->precio_anterior) * 100, 1) . '%';
            }

            return [
                $i + 1,
                $r->entidad_tipo,
                $r->entidad_id,
                $r->campo === 'precio_venta' ? 'Precio venta' : 'Precio compra neto',
                $r->precio_anterior !== null ? (float) $r->precio_anterior : 'Precio inicial',
                (float) $r->precio_nuevo,
                $variacion ?? '-',
                $r->usuario,
                Carbon::parse($r->fecha_cambio)->format('d-m-Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#', 'Tipo', 'ID entidad', 'Campo', 'Precio anterior', 'Precio nuevo', 'Variación %', 'Usuario', 'Fecha cambio',
        ];
    }

    public function title(): string
    {
        return 'Historial precios';
    }
}
