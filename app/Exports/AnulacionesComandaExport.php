<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnulacionesComandaExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected $desde,
        protected $hasta
    ) {
    }

    public function collection(): Collection
    {
        $desde = Carbon::parse($this->desde)->startOfDay();
        $hasta = Carbon::parse($this->hasta)->endOfDay();

        $rows = DB::table('anulaciones_productos_comanda as apc')
            ->join('comandas as com', 'com.id', '=', 'apc.comanda_id')
            ->leftJoin('mesas as me', 'me.id', '=', 'com.mesa_id')
            ->leftJoin('users as u', 'u.id', '=', 'apc.usuario_id')
            ->leftJoin('users as g', 'g.id', '=', 'com.garzon_id')
            ->leftJoin('productos as p', 'p.id', '=', 'apc.producto_id')
            ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
            ->whereBetween('apc.created_at', [$desde, $hasta])
            ->selectRaw("DATE_FORMAT(apc.created_at, '%d/%m/%Y %H:%i') as fecha, COALESCE(com.numero_comanda, CONCAT('COM-', apc.comanda_id)) as numero_comanda, COALESCE(me.nombre, CONCAT('Mesa ', com.mesa_id)) as mesa, COALESCE(g.name, 'Sin garzón') as garzon, COALESCE(p.descripcion, CONCAT('Producto #', apc.producto_id)) as producto, COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria, apc.cantidad, COALESCE(p.precio_venta, 0) as precio_referencia, (apc.cantidad * COALESCE(p.precio_venta, 0)) as monto_referencia, COALESCE(u.name, 'Sin usuario') as usuario, apc.motivo")
            ->orderByDesc('apc.created_at')
            ->get();

        return $rows->map(fn ($row) => [
            $row->fecha,
            $row->numero_comanda,
            $row->mesa,
            $row->garzon,
            $row->producto,
            $row->categoria,
            (float) $row->cantidad,
            (float) $row->precio_referencia,
            (float) $row->monto_referencia,
            $row->usuario,
            $row->motivo,
        ]);
    }

    public function headings(): array
    {
        return ['Fecha eliminación', 'Comanda', 'Mesa', 'Garzón', 'Producto', 'Categoría', 'Cantidad', 'Precio referencia', 'Monto referencia', 'Usuario', 'Motivo'];
    }

    public function title(): string
    {
        return 'Anulaciones comandas';
    }
}