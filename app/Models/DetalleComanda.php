<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleComanda extends Model
{
    use HasFactory;

    protected $table = 'detalle_comandas';

    protected $fillable = [
        'comanda_id',
        'producto_id',
        'tipo_item',
        'receta_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    // Relaciones
    public function comanda()
    {
        return $this->belongsTo(Comanda::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }
}
