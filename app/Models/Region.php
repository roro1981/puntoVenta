<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = 'regiones';

    protected $fillable = [
        'id',
        'nom_region'
    ];

    public $timestamps = false;

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'region_id');
    }
    public function comunas()
    {
        return $this->hasMany(Comuna::class, 'id_region');
    }
}
