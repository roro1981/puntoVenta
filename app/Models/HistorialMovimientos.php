<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialMovimientos extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'historial_movimientos';

    protected $fillable = [
        'producto_id',
        'cantidad',
        'stock',
        'tipo_mov',
        'fecha',
        'num_doc',
        'obs',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }

    
}
