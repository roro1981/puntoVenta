<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Boleta extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'boletas';
    protected $casts = [
        'fecha_boleta' => 'datetime',
    ];
    protected $fillable = [
        'uuid',
        'num_boleta',
        'prov_id',
        'tot_boleta',
        'fecha_boleta',
        'foto',
        'usuario_foto',
        'fec_creacion',
        'user_creacion',
        'fec_modificacion',
        'user_modificacion'
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleBoleta::class, 'num_boleta', 'num_boleta');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'prov_id', 'id');
    }

    public static function grabarBoleta(array $datos): self
    {
        $id_prov = Proveedor::where('uuid', $datos['prov'])->value('id');

        return self::create([
            'uuid' => Str::uuid(),
            'num_boleta' => $datos['num_doc'],
            'prov_id' => $id_prov,
            'tot_boleta' => 0,
            'fecha_boleta' => $datos['fec_doc'],
            'fec_creacion' => now(),
            'user_creacion' => auth()->user()->name
        ]);
    }
}
