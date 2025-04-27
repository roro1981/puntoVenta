<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RangoPrecio extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'rangos_precios';

    protected $fillable = [
        'uuid',
        'producto_id',
        'cantidad_minima',
        'cantidad_maxima',
        'precio_unitario',
        'fec_creacion',
        'user_creacion',
        'fec_modificacion',
        'user_modificacion'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function crearRango(array $data)
    {

        $producto = Producto::where('uuid', $data['uuid'])->first();

        $this->uuid = Str::uuid();
        $this->producto_id = $producto->id;
        $this->cantidad_minima = $data['cantidad_minima'] ?? 1;
        $this->cantidad_maxima = $data['cantidad_maxima'] !== '' ? $data['cantidad_maxima'] : null;
        $this->precio_unitario = $data['precio_unitario'];
        $this->fec_creacion = now();
        $this->user_creacion = auth()->user()->name;

        $this->save();

        return $this;
    }

    public function actualizarRango(array $data)
    {
        $producto = Producto::where('uuid', $data['uuid'])->firstOrFail();

        $this->producto_id = $producto->id;
        $this->cantidad_minima = $data['cantidad_minima'] ?? 1;
        $this->cantidad_maxima = $data['cantidad_maxima'] !== '' ? $data['cantidad_maxima'] : null;
        $this->precio_unitario = $data['precio_unitario'];
        $this->fec_modificacion = now();
        $this->user_modificacion = auth()->user()->name;
        $this->save();

        return $this;
    }
}
