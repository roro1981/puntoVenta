<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleta extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'boletas';

    protected $fillable = [
        'uuid',
        'num_boleta',
        'prov_id',
        'tot_boleta',
        'fecha_boleta',
        'foto',
        'usuario_foto',
        'fec_creacion',
        'user_creacion',
        'fec_modificacion',
        'user_modificacion'
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleBoleta::class, 'num_boleta', 'num_boleta');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'prov_id', 'id');
    }
}
