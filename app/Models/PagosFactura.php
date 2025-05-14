<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagosFactura extends Model
{
    protected $table = 'pagos_factura';
 
    public $timestamps = false;

    protected $fillable = [
        'idpago',
        'nro_factura',
        'monto_pago',
        'fpago',
        'num_docu',
        'fecha_pago',
        'usuario',
    ];
    
    public function factura()
    {
        return $this->belongsTo(Facturas::class, 'nro_factura', 'num_factura');
    }
}

