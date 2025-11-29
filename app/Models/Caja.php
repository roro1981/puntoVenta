<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'cajas';
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'monto_ventas',
        'monto_efectivo',
        'monto_tarjeta',
        'monto_transferencia',
        'monto_otros',
        'monto_final_declarado',
        'diferencia',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_inicial' => 'decimal:2',
        'monto_ventas' => 'decimal:2',
        'monto_efectivo' => 'decimal:2',
        'monto_tarjeta' => 'decimal:2',
        'monto_transferencia' => 'decimal:2',
        'monto_otros' => 'decimal:2',
        'monto_final_declarado' => 'decimal:2',
        'diferencia' => 'decimal:2'
    ];

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con las ventas de este turno de caja
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'caja_id');
    }

    /**
     * Scope para obtener cajas abiertas
     */
    public function scopeAbiertas($query)
    {
        return $query->where('estado', 'abierta');
    }

    /**
     * Scope para obtener cajas cerradas
     */
    public function scopeCerradas($query)
    {
        return $query->where('estado', 'cerrada');
    }

    /**
     * Obtener caja abierta del usuario actual
     */
    public static function cajaAbiertaUsuario($userId)
    {
        return self::where('user_id', $userId)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();
    }

    /**
     * Calcular monto esperado al cierre
     */
    public function montoEsperado()
    {
        return $this->monto_inicial + $this->monto_ventas;
    }

    /**
     * Verificar si hay diferencia en el cierre
     */
    public function tieneDiferencia()
    {
        return $this->diferencia !== null && abs($this->diferencia) > 0;
    }
}
