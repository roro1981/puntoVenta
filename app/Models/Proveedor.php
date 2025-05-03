<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'uuid',
        'rut',
        'razon_social',
        'nombre_fantasia',
        'giro',
        'direccion',
        'region_id',
        'comuna_id',
        'telefono',
        'email',
        'pagina_web',
        'contacto_nombre',
        'contacto_email',
        'contacto_telefono',
        'estado',
        'fec_creacion',
        'user_creacion',
        'fec_modificacion',
        'user_modificacion',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function comuna()
    {
        return $this->belongsTo(Comuna::class, 'comuna_id');
    }
}
