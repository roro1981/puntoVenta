<?php

namespace App\Models;

use App\Models\Producto;
use App\Models\PromocionDetalle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Promocion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'promociones';

    protected $fillable = [
        'uuid',
        'codigo',
        'nombre',
        'categoria_id',
        'precio_costo',
        'precio_venta',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'fec_creacion',
        'user_creacion',
        'fec_modificacion',
        'user_modificacion'
    ];

    public function detallePromocion()
    {
        return $this->hasMany(PromocionDetalle::class, 'promo_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public static function crearPromocionConProductos(array $data)
    {
        DB::beginTransaction();
        try {
            $promocion = self::create([
                'uuid' => Str::uuid(),
                'codigo' => Str::upper($data['codigo']),
                'nombre' => Str::upper($data['nombre']),
                'precio_costo' => $data['precio_costo'],
                'precio_venta' => $data['precio_venta'],
                'categoria_id' => $data['categoria'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'fec_creacion' => now(),
                'user_creacion' => auth()->user()->name,
                'estado' => 'Activo',
            ]);

            if (!empty($data['detallePromo'])) {
                foreach ($data['detallePromo'] as $prom) {
                    $producto = Producto::where('codigo', $prom['codigo'])->first();

                    $promocion->detallePromocion()->create([
                        'uuid' => Str::uuid(),
                        'promo_id' => $promocion->id,
                        'producto_id' => $producto->id,
                        'cantidad' => $prom['cantidad'] ?? 1,
                        'unidad' => $producto->unidad_medida
                    ]);
                }
            }

            DB::commit();
            return $promocion;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function actualizarPromocionConProductos($uuid, array $data)
    {
        DB::beginTransaction();
        try {
            $promocion = self::where('uuid', $uuid)->firstOrFail();

            $promocion->update([
                'codigo'        => Str::upper($data['codigo']),
                'nombre'        => Str::upper($data['nombre']),
                'precio_costo'  => $data['precio_costo'],
                'precio_venta'  => $data['precio_venta'],
                'categoria_id'  => $data['categoria'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'fec_modificacion' => now(),
                'user_modificacion' => auth()->user()->name,
            ]);

            $promocion->detallePromocion()->delete();

            foreach ($data['productos'] as $prom) {
                $producto = Producto::where('codigo', $prom['codigo'])->first();

                $promocion->detallePromocion()->create([
                    'uuid'         => Str::uuid(),
                    'promo_id'    =>  $promocion->id,
                    'producto_id'  => $producto->id,
                    'cantidad'     => $prom['cantidad'],
                    'unidad'       => $producto->unidad_medida,
                ]);
            }

            DB::commit();
            return $promocion;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function eliminarPromocion($uuid)
    {
        $promocion = self::where('uuid', $uuid)->firstOrFail();
        $promocion->estado = 'Inactivo';
        $promocion->fec_modificacion = now();
        $promocion->user_modificacion = auth()->user()->name;
        $promocion->save();

        return $promocion;
    }
}
