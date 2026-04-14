<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreventaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'total'                                => 'required|integer|min:1',
            'total_descuentos'                     => 'nullable|integer|min:0',
            'fecha_venta'                          => 'nullable|date_format:Y-m-d H:i:s',
            'detalles'                             => 'required|array|min:1',
            'detalles.*.producto_uuid'             => 'nullable|uuid',
            'detalles.*.promo_id'                  => 'nullable|integer|exists:promociones,id',
            'detalles.*.descripcion_producto'      => 'required|string|max:255',
            'detalles.*.cantidad'                  => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario'           => 'required|integer|min:0',
            'detalles.*.descuento_porcentaje'      => 'nullable|numeric|min:0|max:100',
            'detalles.*.subtotal_linea'            => 'required|integer|min:0',
        ];
    }
}
