<?php

namespace App\Models;

use App\Models\Boleta;
use App\Models\Comuna;
use App\Models\Region;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'proveedores';

    protected $fillable = [
        'id',
        'uuid',
        'rut',
        'razon_social',
        'nombre_fantasia',
        'giro',
        'fpago_id',
        'direccion',
        'region_id',
        'comuna_id',
        'telefono',
        'email',
        'pagina_web',
        'contacto_nombre',
        'contacto_email',
        'contacto_telefono',
        'estado',
        'fec_creacion',
        'user_creacion',
        'fec_modificacion',
        'user_modificacion',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function comuna()
    {
        return $this->belongsTo(Comuna::class, 'comuna_id');
    }

    public function boletas()
    {
        return $this->hasMany(Boleta::class, 'prov_id', 'id');
    }

    public function facturas()
    {
        return $this->hasMany(Facturas::class, 'prov_id', 'id');
    }

    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class, 'fpago_id', 'id');
    }

    public static function storeProv($provRequest)
    {
        return Proveedor::create([
            'uuid' => Str::uuid(),
            'rut' => $provRequest['rut'],
            'razon_social' => $provRequest['razon_social'],
            'nombre_fantasia' => $provRequest['nombre_fantasia'],
            'giro' => $provRequest['giro'],
            'fpago_id' => $provRequest['fpago'],
            'direccion' => $provRequest['direccion'],
            'region_id' => $provRequest['region'],
            'comuna_id' => $provRequest['comuna'],
            'telefono' => $provRequest['telefono'],
            'email' => $provRequest['email'],
            'pagina_web' => $provRequest['pagina_web'],
            'contacto_nombre' => $provRequest['contacto_nombre'],
            'contacto_email' => $provRequest['contacto_email'],
            'contacto_telefono' => $provRequest['contacto_telefono'],
            'estado' => 'Activo',
            'fec_creacion' => now(),
            'user_creacion' => auth()->user()->name
        ]);
    }

    public function updateProv($provRequest)
    {
        $data = [
            'razon_social' => $provRequest['razon_social'],
            'nombre_fantasia' => $provRequest['nombre_fantasia'],
            'giro' => $provRequest['giro'],
            'fpago_id' => $provRequest['fpago'],
            'direccion' => $provRequest['direccion'],
            'region_id' => $provRequest['region'],
            'comuna_id' => $provRequest['comuna'],
            'telefono' => $provRequest['telefono'],
            'email' => $provRequest['email'],
            'pagina_web' => $provRequest['pagina_web'],
            'contacto_nombre' => $provRequest['contacto_nombre'],
            'contacto_email' => $provRequest['contacto_email'],
            'contacto_telefono' => $provRequest['contacto_telefono'],
            'fec_modificacion' => now(),
            'user_modificacion' => auth()->user()->name
        ];

        $this->update($data);
    }

    public function deleteProv()
    {
        $this->update([
            'estado' => 'Inactivo',
            'fec_modificacion' => now(),
            'user_modificacion' => auth()->user()->name
        ]);
    }
}
