<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;
    protected $table = 'categorias';

    protected $fillable = [
        'id',
        'descripcion_categoria',
        'estado_categoria'
    ];

    public $timestamps = false;

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public static function storeCategory($categoriaRequest)
    {
         return Categoria::create([
             'descripcion_categoria' => strtoupper($categoriaRequest['descripcion_categoria']),
             'estado_categoria' => 1
         ]);
    } 
    
    public function updateCategory($categoriaRequest)
    {
        $data = [
            'descripcion_categoria' => strtoupper($categoriaRequest['descripcion_categoria'])
        ];
        $this->update($data);
    }

    public function deleteCategory()
     {
         $this->update([
             'estado_categoria' => 0
         ]);
     }
}
