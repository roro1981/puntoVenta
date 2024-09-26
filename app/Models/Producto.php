<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'precio_compra_bruto',
        'precio_venta',
        'stock',
        'stock_minimo',
        'categoria_id',
        'tipo',
        'impuesto1',
        'impuesto2',
        'imagen',
        'estado',
        'descrip_receta',
        'fec_creacion',
        'user_creacion',
        'fec_modificacion',
        'user_modificacion',
        'fec_eliminacion',
        'user_eliminacion',
    ];

    public $timestamps = false;

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
