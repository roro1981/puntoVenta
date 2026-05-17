<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnulacionProductoComanda extends Model
{
    use HasFactory;

    protected $table = 'anulaciones_productos_comanda';

    protected $fillable = [
        'comanda_id',
        'producto_id',
        'usuario_id',
        'motivo',
        'cantidad',
    ];
}