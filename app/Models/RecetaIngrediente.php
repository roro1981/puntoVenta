<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetaIngrediente extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'receta_ingredientes';

    protected $fillable = [
        'uuid',
        'cantidad',
        'unidad',
        'receta_id',
        'producto_id',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
