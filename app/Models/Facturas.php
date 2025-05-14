<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Facturas extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'facturas';

    protected $fillable = [
        'uuid',
        'num_factura',
        'prov_id',
        'neto',
        'impuestos',
        'desglose_impuestos',
        'total_fact',
        'fecha_doc',
        'dias',
        'fpago',
        'vencimiento',
        'fec_creacion',
        'estado',
        'user_creacion',
        'foto',
        'usuario_foto',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class, 'num_factura', 'num_factura');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'prov_id', 'id');
    }

    public function pagos()
    {
        return $this->hasMany(PagosFactura::class, 'nro_factura', 'num_factura');
    }

    public static function iniciarCabecera(object $datos): self
    {
        $id_prov=Proveedor::where('uuid', $datos->prov)->value('id');
        return self::create([
            'uuid' =>  Str::uuid(),
            'num_factura' => $datos->num_doc,
            'prov_id' => $id_prov,
            'neto' => 0,
            'impuestos' => $datos->impuestos,
            'desglose_impuestos' => $datos->desc_impuestos,
            'total_fact' => 0,
            'fecha_doc' => $datos->fec_doc,
            'dias' => $datos->dias,
            'fpago' => $datos->f_pago,
            'vencimiento' => $datos->venc_doc,
            'estado' => 'NP',
            'fec_creacion' => now(),
            'user_creacion' => auth()->user()->name,
        ]);
    }
}
