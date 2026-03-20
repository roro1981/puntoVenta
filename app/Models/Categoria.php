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
        'estado_categoria',
        'fec_creacion',
        'user_creacion',
        'fec_eliminacion',
        'user_eliminacion',
    ];

    public $timestamps = false;

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'categoria_id');
    }

    public function promociones()
    {
        return $this->hasMany(Promocion::class, 'categoria_id');
    }

    public static function storeCategory($categoriaRequest)
    {
        return Categoria::create([
            'descripcion_categoria' => strtoupper($categoriaRequest['descripcion_categoria']),
            'estado_categoria' => 1,
            'fec_creacion' => now(),
            'user_creacion' => self::currentUserName(),
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
            'estado_categoria' => 0,
            'fec_eliminacion' => now(),
            'user_eliminacion' => self::currentUserName(),
        ]);
    }

    public function reactivateCategory(): void
    {
        $this->update([
            'estado_categoria' => 1,
            'fec_eliminacion' => null,
            'user_eliminacion' => null,
        ]);
    }

    private static function currentUserName(): string
    {
        return optional(auth()->user())->name ?? 'SISTEMA';
    }
}
