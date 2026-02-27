<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comanda extends Model
{
    use HasFactory;

    protected $table = 'comandas';

    protected $fillable = [
        'mesa_id',
        'user_id',
        'garzon_id',
        'numero_comanda',
        'estado',
        'total',
        'subtotal',
        'impuestos',
        'propina',
        'incluye_propina',
        'comensales',
        'fecha_apertura',
        'fecha_cierre',
        'observaciones'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'propina' => 'decimal:2',
        'incluye_propina' => 'boolean',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime'
    ];

    // Relaciones
    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function garzon()
    {
        return $this->belongsTo(Garzon::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleComanda::class);
    }
}
