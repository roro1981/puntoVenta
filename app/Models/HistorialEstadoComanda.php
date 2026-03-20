<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstadoComanda extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'historial_estados_comandas';

    protected $fillable = [
        'comanda_id',
        'mesa_id',
        'estado_anterior',
        'estado_nuevo',
        'accion',
        'usuario_id',
        'fecha_cambio',
        'observacion',
    ];

    public function comanda()
    {
        return $this->belongsTo(Comanda::class, 'comanda_id', 'id');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }
}
