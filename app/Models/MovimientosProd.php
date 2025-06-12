<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientosProd extends Model
{
    use HasFactory;

    protected $table = 'movimientos_prod';

    public $timestamps = false;

    protected $fillable = [
        'producto_id',
        'cantidad',
        'tipo_movi',
        'obs',
        'fec_mov',
        'usuario_mov',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'prod_id', 'id');
    }

    
}
