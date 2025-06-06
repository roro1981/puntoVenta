<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comuna extends Model
{
    use HasFactory;

    protected $table = 'comunas';

    protected $fillable = [
        'id',
        'nom_comuna',
        'id_region'
    ];

    public $timestamps = false;

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'comuna_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'id_region');
    }
}
