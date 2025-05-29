<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleFactura extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'detalle_factura';

    protected $fillable = [
        'uuid',
        'num_factura',
        'cod_producto',
        'cantidad',
        'precio',
        'descuento',
        'imp1',
        'imp2',
    ];

    public static function grabarDetalleFactura(object $item): self
    {
        return self::create([
            'uuid' =>  Str::uuid(),
            'num_factura' => $item->nfact,
            'cod_producto' => $item->cod,
            'cantidad' => $item->cant,
            'precio' => $item->precio,
            'descuento' => $item->descu,
            'imp1' => $item->imp1,
            'imp2' => $item->imp2,
        ]);
    }
}
