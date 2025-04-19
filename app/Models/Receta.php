<?php

namespace App\Models;

use App\Models\Producto;
use App\Models\RecetaIngrediente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Receta extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'recetas';

    protected $fillable = [
        'uuid',
        'codigo',
        'nombre',
        'descripcion',
        'precio_costo',
        'precio_venta',
        'imagen',
        'estado',
        'fec_creacion',
        'user_creacion',
        'fec_modificacion',
        'user_modificacion',
        'categoria_id'
    ];

    public function ingredientes()
    {
        return $this->hasMany(RecetaIngrediente::class, 'receta_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public static function crearRecetaConIngredientes(array $data)
    {
        DB::beginTransaction();
        try {
            $receta = self::create([
                'uuid' => Str::uuid(),
                'codigo' => Str::upper($data['codigo']),
                'nombre' => Str::ucfirst($data['nombre']),
                'precio_costo' => $data['precio_costo'],
                'precio_venta' => $data['precio_venta'],
                'categoria_id' => $data['categoria'],
                'descripcion' => $data['descripcion'],
                'imagen' => $data['foto'] ?? null,
                'fec_creacion' => now(),
                'user_creacion' => auth()->user()->name,
                'estado' => 'Activo',
            ]);

            if (!empty($data['ingredientes'])) {
                foreach ($data['ingredientes'] as $ing) {
                    $producto = Producto::where('codigo', $ing['codigo'])->first();

                    $receta->ingredientes()->create([
                        'uuid' => Str::uuid(),
                        'receta_id' => $receta->id,
                        'producto_id' => $producto->id,
                        'cantidad' => $ing['cantidad'] ?? 1,
                        'unidad' => $producto->unidad_medida
                    ]);
                }
            }

            DB::commit();
            return $receta;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function actualizarRecetaConIngredientes($uuid, array $data)
    {
        DB::beginTransaction();
        try {
            $receta = self::where('uuid', $uuid)->firstOrFail();

            $receta->update([
                'codigo'        => Str::upper($data['codigo']),
                'nombre'        => Str::ucfirst($data['nombre']),
                'precio_costo'  => $data['precio_costo'],
                'precio_venta'  => $data['precio_venta'],
                'categoria_id'  => $data['categoria'],
                'descripcion'   => $data['descripcion'],
                'imagen'        => $data['foto'] ?? $receta->imagen,
                'fec_modificacion' => now(),
                'user_modificacion' => auth()->user()->name,
            ]);

            $receta->ingredientes()->delete();

            foreach ($data['ingredientes'] as $ing) {
                $producto = Producto::where('codigo', $ing['codigo'])->first();

                $receta->ingredientes()->create([
                    'uuid'         => Str::uuid(),
                    'receta_id'    => $receta->id,
                    'producto_id'  => $producto->id,
                    'cantidad'     => $ing['cantidad'],
                    'unidad'       => $producto->unidad_medida,
                ]);
            }

            DB::commit();
            return $receta;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function eliminarReceta($uuid)
    {
        $receta = self::where('uuid', $uuid)->firstOrFail();
        $receta->estado = 'Inactivo';
        $receta->fec_modificacion = now();
        $receta->user_modificacion = auth()->user()->name;
        $receta->save();

        return $receta;
    }
}
