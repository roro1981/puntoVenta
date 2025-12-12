<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalles_ventas';
    protected $fillable = [
        'venta_id',
        'producto_uuid',
        'descripcion_producto',
        'cantidad',
        'precio_unitario',
        'descuento_porcentaje',
        'subtotal_linea',
        'anulado',
        'fecha_anulacion',
        'user_anulacion_id',
        'motivo_anulacion',
    ];
    public $timestamps = false;

    /**
     * Relación: un detalle pertenece a una venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /**
     * Casteos de atributos
     */
    protected $casts = [
        'cantidad' => 'float',
        'descuento_porcentaje' => 'float',
        'anulado' => 'boolean',
        'fecha_anulacion' => 'datetime',
    ];

    /**
     * Relación: usuario que anuló el detalle
     */
    public function usuarioAnulacion()
    {
        return $this->belongsTo(User::class, 'user_anulacion_id');
    }
}