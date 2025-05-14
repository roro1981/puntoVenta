<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'formas_pago';

    protected $fillable = [
        'id',
        'descripcion_pago',
    ];

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'fpago_id', 'id');
    }
}
