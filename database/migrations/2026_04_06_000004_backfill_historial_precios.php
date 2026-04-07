<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ahora = now();

        // ── PRODUCTOS ────────────────────────────────────────────────
        $productos = DB::table('productos')
            ->whereNull('fec_eliminacion')
            ->get(['id', 'precio_compra_neto', 'precio_venta', 'user_creacion', 'fec_creacion']);

        foreach ($productos as $p) {
            foreach (['precio_compra_neto', 'precio_venta'] as $campo) {
                $yaExiste = DB::table('historial_precios')
                    ->where('entidad_tipo', 'PRODUCTO')
                    ->where('entidad_id', $p->id)
                    ->where('campo', $campo)
                    ->exists();

                if (!$yaExiste) {
                    $valor = $campo === 'precio_compra_neto'
                        ? (float) $p->precio_compra_neto
                        : (float) $p->precio_venta;

                    DB::table('historial_precios')->insert([
                        'entidad_tipo'    => 'PRODUCTO',
                        'entidad_id'      => $p->id,
                        'campo'           => $campo,
                        'precio_anterior' => null,
                        'precio_nuevo'    => $valor,
                        'usuario'         => $p->user_creacion ?? 'SISTEMA',
                        'fecha_cambio'    => $p->fec_creacion ?? $ahora,
                    ]);
                }
            }
        }

        // ── RECETAS ──────────────────────────────────────────────────
        $recetas = DB::table('recetas')
            ->where('estado', '!=', 'Inactivo')
            ->get(['id', 'precio_venta', 'user_creacion', 'fec_creacion']);

        foreach ($recetas as $r) {
            $yaExiste = DB::table('historial_precios')
                ->where('entidad_tipo', 'RECETA')
                ->where('entidad_id', $r->id)
                ->where('campo', 'precio_venta')
                ->exists();

            if (!$yaExiste) {
                DB::table('historial_precios')->insert([
                    'entidad_tipo'    => 'RECETA',
                    'entidad_id'      => $r->id,
                    'campo'           => 'precio_venta',
                    'precio_anterior' => null,
                    'precio_nuevo'    => (float) $r->precio_venta,
                    'usuario'         => $r->user_creacion ?? 'SISTEMA',
                    'fecha_cambio'    => $r->fec_creacion ?? $ahora,
                ]);
            }
        }

        // ── PROMOCIONES ──────────────────────────────────────────────
        $promociones = DB::table('promociones')
            ->where('estado', '!=', 'Inactivo')
            ->get(['id', 'precio_venta', 'user_creacion', 'fec_creacion']);

        foreach ($promociones as $p) {
            $yaExiste = DB::table('historial_precios')
                ->where('entidad_tipo', 'PROMOCION')
                ->where('entidad_id', $p->id)
                ->where('campo', 'precio_venta')
                ->exists();

            if (!$yaExiste) {
                DB::table('historial_precios')->insert([
                    'entidad_tipo'    => 'PROMOCION',
                    'entidad_id'      => $p->id,
                    'campo'           => 'precio_venta',
                    'precio_anterior' => null,
                    'precio_nuevo'    => (float) $p->precio_venta,
                    'usuario'         => $p->user_creacion ?? 'SISTEMA',
                    'fecha_cambio'    => $p->fec_creacion ?? $ahora,
                ]);
            }
        }
    }

    public function down(): void
    {
        // No revertible: eliminaría solo los registros con precio_anterior null
        // generados por este backfill, lo cual es difícil de diferenciar con seguridad.
    }
};
