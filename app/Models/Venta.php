<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';
    protected $fillable = [
        'total',
        'total_descuentos',
        'user_id',
        'caja_id',
        'forma_pago',
        'estado',
        'fecha_venta',
    ];
    public $timestamps = false;

    /**
     * Relación: una venta pertenece a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: una venta pertenece a una caja
     */
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    /**
     * Relación: una venta tiene muchos detalles
     */
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /**
     * Relación: una venta tiene muchas formas de pago
     */
    public function formasPago()
    {
        return $this->hasMany(FormaPagoVenta::class, 'venta_id');
    }

    /**
     * Casteos de atributos
     */
    protected $casts = [
        'fecha_venta' => 'datetime',
    ];
}
