<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:100',
            'descripcion' => 'required|string|max:255',
            'precio_compra_neto' => 'required|integer|min:0',
            'impuesto_1' => 'required|numeric|min:0|max:99.9',
            'impuesto_2' => 'nullable|numeric|min:0|max:99.9',
            'precio_compra_bruto' => 'required|integer|min:0',
            'precio_venta' => 'required|integer|min:0',
            'stock_minimo' => 'required|numeric|min:0|max:999.9',
            'categoria' => 'required|exists:categorias,id',
            'tipo' => 'required|in:P,S,I,PR,R',
            'nom_foto' => 'nullable|string|max:255',
            'descrip_receta' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'precio_compra_bruto.required' => 'El precio de compra bruto es obligatorio.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'tipo.required' => 'El tipo de producto es obligatorio.',
            'tipo.in' => 'El tipo de producto debe ser uno de los siguientes: PRODUCTO, NO AFECTO A STOCK, INSUMO.',
            'impuesto_1.required' => 'El primer impuesto es obligatorio.'
        ];
    }
}
