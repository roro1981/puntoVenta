<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialPrecio extends Model
{
    public $timestamps = false;

    protected $table = 'historial_precios';

    protected $fillable = [
        'entidad_tipo',
        'entidad_id',
        'campo',
        'precio_anterior',
        'precio_nuevo',
        'usuario',
        'fecha_cambio',
    ];

    /**
     * Registra uno o varios cambios de precio en lote.
     *
     * @param string      $tipo   PRODUCTO | RECETA | PROMOCION
     * @param int         $id     ID de la entidad
     * @param array       $campos ['precio_venta' => [anterior, nuevo], ...]
     * @param string|null $usuario
     */
    public static function registrar(string $tipo, int $id, array $campos, ?string $usuario = null): void
    {
        $usuario = $usuario ?? (optional(auth()->user())->name ?? 'SISTEMA');
        $ahora   = now();

        foreach ($campos as $campo => [$anterior, $nuevo]) {
            // Solo registrar si el precio efectivamente cambió (o es creación, anterior=null)
            if ($anterior === null || (float) $anterior !== (float) $nuevo) {
                self::create([
                    'entidad_tipo'   => $tipo,
                    'entidad_id'     => $id,
                    'campo'          => $campo,
                    'precio_anterior' => $anterior,
                    'precio_nuevo'   => $nuevo,
                    'usuario'        => $usuario,
                    'fecha_cambio'   => $ahora,
                ]);
            }
        }
    }
}
