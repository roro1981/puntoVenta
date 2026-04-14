<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoVenta extends Model
{
    protected $table = 'historial_estados_ventas';
    public $timestamps = false;

    protected $fillable = [
        'venta_id',
        'estado_anterior',
        'estado_nuevo',
        'accion',
        'usuario_id',
        'fecha_cambio',
        'observacion',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
