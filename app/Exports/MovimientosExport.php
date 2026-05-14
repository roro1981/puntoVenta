<?php

namespace App\Exports;

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
        $producto = \App\Models\Producto::where('uuid', $this->uuid)->first();
        $productoNombreBase = optional($producto)->descripcion ?? 'Sin nombre';
        $esServicio = strtoupper((string) optional($producto)->tipo) === 'S';

        return collect($movs)->map(function ($mov) use ($esServicio, $productoNombreBase) {
            $signo = match ($mov->tipo_mov) {
                'ENTRADA', 'FACTURA COMPRA', 'BOLETA COMPRA' => '(+)',
                'SALIDA', 'MERMA', 'VENTA', 'VENTA (RECETA)', 'VENTA (PROMO)' => '(-)',
                default => '',
            };

            $nombreProducto = $productoNombreBase;
            if (isset($mov->producto) && isset($mov->producto->descripcion)) {
                $nombreProducto = $mov->producto->descripcion;
            }

            return [
                'fecha'         => \Carbon\Carbon::parse($mov->fecha)->format('d-m-Y H:i:s'),
                'producto'      => $nombreProducto,
                'tipo_mov'      => $mov->tipo_mov . ' ' . $signo,
                'cantidad'      => $mov->cantidad,
                'stock'         => $esServicio ? 'No aplica' : $mov->stock,
                'observacion'   => $mov->obs,
            ];
        });
    }

    public function headings(): array
    {
        return ['Fecha', 'Producto', 'Tipo Movimiento', 'Cantidad', 'Stock', 'Observación'];
    }
}