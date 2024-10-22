<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'precio_compra_bruto',
        'precio_venta',
        'stock',
        'stock_minimo',
        'categoria_id',
        'tipo',
        'impuesto1',
        'impuesto2',
        'imagen',
        'estado',
        'descrip_receta',
        'fec_creacion',
        'user_creacion',
        'fec_modificacion',
        'user_modificacion',
        'fec_eliminacion',
        'user_eliminacion',
    ];

    public $timestamps = false;

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function crearProducto(array $data)
    {
        $this->codigo = strtoupper($data['codigo']);
        $this->descripcion = strtoupper($data['descripcion']);
        $this->precio_compra_neto = $data['precio_compra_neto'];
        $this->precio_compra_bruto = $data['precio_compra_bruto'];
        $this->precio_venta = $data['precio_venta'];
        $this->impuesto1 = $data['impuesto_1'];
        $this->impuesto2 = $data['impuesto_2'];
        $this->categoria_id = $data['categoria'];
        $this->stock_minimo = $data['stock_minimo'] ?? 0;
        $this->tipo = $data['tipo'];
        $this->imagen = $data['nom_foto'];
        $this->estado = 'Activo';
        $this->fec_creacion = now();
        $this->user_creacion = auth()->user()->name;

        $this->save();

        return $this;
    }
}
