<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garzon extends Model
{
    use HasFactory;

    protected $table = 'garzones';

    protected $fillable = [
        'nombre',
        'apellido',
        'rut',
        'telefono',
        'email',
        'estado'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function comandas()
    {
        return $this->hasMany(Comanda::class, 'garzon_id');
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->apellido;
    }
}
