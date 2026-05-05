<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\RangoPrecio;

/**
 * Servicio centralizado para la resolución de precio unitario por rango de cantidad.
 * Unifica la lógica duplicada entre VentasController y ComandasController.
 */
class PrecioService
{
    /**
     * Resuelve el precio unitario para un producto dado una cantidad.
     * Si existe un rango de precio que aplique, devuelve el precio del rango.
     * De lo contrario, devuelve el precio_venta base del producto.
     */
    public static function resolver(Producto $producto, float $cantidad): float
    {
        if ($cantidad <= 0) {
            return (float) $producto->precio_venta;
        }

        $rango = RangoPrecio::where('producto_id', $producto->id)
            ->where('cantidad_minima', '<=', $cantidad)
            ->where(function ($query) use ($cantidad) {
                $query->whereNull('cantidad_maxima')
                    ->orWhere('cantidad_maxima', 0)
                    ->orWhere('cantidad_maxima', '>=', $cantidad);
            })
            ->orderByDesc('cantidad_minima')
            ->first();

        return $rango ? (float) $rango->precio_unitario : (float) $producto->precio_venta;
    }
}
