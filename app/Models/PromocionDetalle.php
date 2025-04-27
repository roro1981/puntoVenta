<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromocionDetalle extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'promociones_detalle';

    protected $fillable = [
        'uuid',
        'cantidad',
        'unidad',
        'promo_id',
        'producto_id',
    ];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promo_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
