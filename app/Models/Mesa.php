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
        'activa'
    ];

    protected $casts = [
        'activa' => 'boolean',
        'orden' => 'integer',
        'capacidad' => 'integer'
    ];

    // Relaciones
    public function comandas()
    {
        return $this->hasMany(Comanda::class);
    }

    public function comandaAbierta()
    {
        return $this->hasOne(Comanda::class)->where('estado', 'ABIERTA')->latest();
    }
}
