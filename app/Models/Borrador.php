<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Borrador extends Model
{
    use HasFactory;

    protected $table = 'borradores';

    protected $fillable = [
        'producto',
        'cantidad',
        'precio_venta',
        'descuento',
        'uuid_borrador',
        'fec_creacion',
        'producto_uuid'
    ];

    public $timestamps = false;

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'uuid');
    }

    public static function guardarBorradores(array $productos): void
    {
        $fecha = $fecha ?? Carbon::now();

        foreach ($productos as $item) {
            self::create([
                'producto_uuid'    => $item['product_uuid'],
                'producto'      => $item['descripcion'],
                'cantidad'      => $item['cantidad'],
                'precio_venta'  => $item['precio_venta'],
                'descuento'     => $item['descuento'],
                'uuid_borrador' => $item['uuid_borrador'],
                'fec_creacion'  => $item['fec_creacion']
            ]);
        }
    }

    public static function borrarBorrador($uuid_borrador)
    {
        return self::where('uuid_borrador', $uuid_borrador)->delete();
    }    

    public static function resumen()
    {
        return self::select(
            'uuid_borrador',
            DB::raw("MIN(DATE_FORMAT(fec_creacion, '%d-%m-%Y %H:%i:%s')) AS fecha_creacion"),
            DB::raw('SUM(cantidad) AS total_cantidad'),
            DB::raw('SUM(cantidad * precio_venta) AS total')
        )
        ->groupBy('uuid_borrador')
        ->get();
    }

    public static function obtenerProductosPorUuid($uuid)
    {
        return self::select('cantidad', 'producto', 'precio_venta', 'descuento', 'producto_uuid')
            ->where('uuid_borrador', $uuid)
            ->get();
    }


}
