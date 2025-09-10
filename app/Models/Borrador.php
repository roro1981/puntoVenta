<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
        'product_id'
    ];

    public $timestamps = false;

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'product_id');
    }

    public static function guardarBorradores(array $productos): void
    {
        $fecha = $fecha ?? Carbon::now();

        foreach ($productos as $item) {
            self::create([
                'product_id'    => $item['id'],
                'producto'      => $item['descripcion'],
                'cantidad'      => $item['cantidad'],
                'precio_venta'  => $item['precio_venta'],
                'descuento'     => $item['descuento'],
                'uuid_borrador' => $item['uuid_borrador'],
                'fec_creacion'  => $item['fec_creacion']
            ]);
        }
    }
}
