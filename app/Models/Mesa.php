<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';

    protected $fillable = [
        'nombre',
        'orden',
        'capacidad',
        'activa',
        'reservada',
        'reservada_por_user_id',
        'reservada_at'
    ];

    protected $casts = [
        'activa' => 'boolean',
        'reservada' => 'boolean',
        'orden' => 'integer',
        'capacidad' => 'integer',
        'reservada_at' => 'datetime'
    ];

    // Relaciones
    public function comandas()
    {
        return $this->hasMany(Comanda::class);
    }

    public function comandaAbierta()
    {
        return $this->hasOne(Comanda::class)->whereIn('estado', ['ABIERTA', 'EN CONSUMO', 'PENDIENTE DE PAGO'])->latest();
    }

    public function reservadaPor()
    {
        return $this->belongsTo(User::class, 'reservada_por_user_id');
    }
}
