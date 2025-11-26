<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPagoVenta extends Model
{
    protected $table = 'formas_pago_venta';
    protected $fillable = [
        'venta_id',
        'forma_pago',
        'monto',
    ];
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Relación: una forma de pago pertenece a una venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
