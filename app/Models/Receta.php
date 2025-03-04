<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'recetas';

    protected $fillable = [
        'uuid',
        'codigo',
        'nombre',
        'descripcion',
        'precio_costo',
        'precio_venta',
        'imagen',
        'estado',
        'fec_creacion',
        'user_creacion'
    ];
}
