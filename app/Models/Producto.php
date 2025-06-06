<?php

namespace App\Models;

use App\Models\Categoria;
use App\Models\Receta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'id',
        'uuid',
        'descripcion',
        'codigo',
        'precio_compra_bruto',
        'precio_compra_neto',
        'precio_venta',
        'stock',
        'stock_minimo',
        'categoria_id',
        'tipo',
        'impuesto1',
        'impuesto2',
        'imagen',
        'estado',
        'unidad_medida',
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
        return $this->belongsTo(Categoria::class, 'categoria_id', 'id');
    }

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    public function rangosPrecios()
    {
        return $this->hasMany(RangoPrecio::class);
    }

    public function historialMovimientos()
    {
        return $this->hasMany(HistorialMovimientos::class, 'prod_id', 'id');
    }

    public function movimientosProd()
    {
        return $this->hasMany(MovimientosProd::class, 'prod_id', 'id');
    }
    public function impuesto1()
    {
        return $this->belongsTo(Impuestos::class, 'impuesto1', 'id');
    }

    public function impuesto2()
    {
        return $this->belongsTo(Impuestos::class, 'impuesto2', 'id');
    }
    public function crearProducto(array $data)
    {
        $this->uuid = Str::uuid();
        $this->codigo = strtoupper($data['codigo']);
        $this->descripcion = strtoupper($data['descripcion']);
        $this->precio_compra_neto = $data['precio_compra_neto'];
        $this->precio_compra_bruto = $data['precio_compra_bruto'];
        $this->precio_venta = $data['precio_venta'];
        $this->impuesto1 = $data['impuesto_1'];
        $this->impuesto2 = $data['impuesto_2'];
        $this->categoria_id = $data['categoria'];
        $this->stock_minimo = $data['stock_minimo'] ?? 0;
        $this->unidad_medida = $data['unidad_medida'];
        $this->tipo = $data['tipo'];
        $this->imagen = $data['nom_foto'];
        $this->estado = 'Activo';
        $this->fec_creacion = now();
        $this->user_creacion = auth()->user()->name;

        $this->save();

        return $this;
    }

    public function editarProducto(array $data)
    {
        $this->descripcion = strtoupper($data['descripcion']);
        $this->precio_compra_neto = $data['precio_compra_neto'];
        $this->precio_compra_bruto = $data['precio_compra_bruto'];
        $this->precio_venta = $data['precio_venta'];
        $this->impuesto1 = $data['impuesto_1'];
        $this->impuesto2 = $data['impuesto_2'];
        $this->categoria_id = $data['categoria'];
        $this->stock_minimo = $data['stock_minimo'] ?? 0;
        $this->tipo = $data['tipo'];
        $this->unidad_medida = $data['unidad_medida'];
        $this->imagen = $data['nom_foto'];
        $this->fec_modificacion = now();
        $this->user_modificacion = auth()->user()->name;

        $this->update();

        return $this;
    }

    public function deleteProduct()
    {
        $this->update([
            'estado' => 'Inactivo',
            'fec_eliminacion' => now(),
            'user_eliminacion' => auth()->user()->name,
        ]);
    }
}
