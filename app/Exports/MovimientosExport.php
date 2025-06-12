<?php

namespace App\Exports;

use App\Services\ReportesService;
use Illuminate\Contracts\View\View;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class MovimientosExport implements FromCollection, WithHeadings
{
    protected $tipo;
    protected $uuid;
    protected $desde;
    protected $hasta;

    public function __construct($tipo, $uuid, $desde, $hasta)
    {
        $this->tipo = $tipo;
        $this->uuid = $uuid;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection()
    {
        $service = new \App\Services\ReportesService();
        $movs = $service->traerMovimientos($this->tipo, $this->uuid, $this->desde, $this->hasta);

        return collect($movs)->map(function ($mov) {
            $signo = match ($mov->tipo_mov) {
                'ENTRADA', 'FACTURA COMPRA', 'BOLETA COMPRA' => '(+)',
                'SALIDA', 'MERMA', 'VENTA', 'VENTA (RECETA)', 'VENTA (PROMO)' => '(-)',
                default => '',
            };

            return [
                'fecha'         => \Carbon\Carbon::parse($mov->fecha)->format('d-m-Y H:i:s'),
                'producto'      => optional($mov->producto)->descripcion ?? 'Sin nombre',
                'tipo_mov'      => $mov->tipo_mov . ' ' . $signo,
                'cantidad'      => $mov->cantidad,
                'stock'         => $mov->stock,
                'observacion'   => $mov->obs,
            ];
        });
    }

    public function headings(): array
    {
        return ['Fecha', 'Producto', 'Tipo Movimiento', 'Cantidad', 'Stock', 'Observación'];
    }
}