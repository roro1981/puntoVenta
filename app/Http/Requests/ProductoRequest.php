<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    
    public function rules(): array
    {
        return [
            'precio_compra_bruto' => 'required|integer|min:0',
            'precio_venta' => 'required|integer|min:0',
            'stock' => 'required|numeric|min:0|max:9999.9',
            'stock_minimo' => 'required|numeric|min:0|max:999.9',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo' => 'required|in:P,S,I,PR,R',
            'impuesto1' => 'required|numeric|min:0|max:99.9',
            'impuesto2' => 'nullable|numeric|min:0|max:99.9',
            'imagen' => 'nullable|string|max:255',
            'estado' => 'required|string|in:Activo,Inactivo',
            'descrip_receta' => 'nullable|string',
            'fec_creacion' => 'required|date',
            'user_creacion' => 'required|string|max:100',
            'fec_modificacion' => 'nullable|date',
            'user_modificacion' => 'nullable|string|max:100',
            'fec_eliminacion' => 'nullable|date',
            'user_eliminacion' => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'precio_compra_bruto.required' => 'El precio de compra bruto es obligatorio.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'categoria_id.required' => 'La categoría es obligatoria.',
            'tipo.required' => 'El tipo de producto es obligatorio.',
            'tipo.in' => 'El tipo de producto debe ser uno de los siguientes: PRODUCTO, NO AFECTO A STOCK, INSUMO.',
            'impuesto1.required' => 'El primer impuesto es obligatorio.'
        ];
    }
}
