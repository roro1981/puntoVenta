<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaRequest extends FormRequest
{
    
    public function rules(): array
    {
        return [
            'descripcion_categoria' => 'required|string|max:100',
            'estado_categoria' => 'required|integer|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'descripcion_categoria.required' => 'La descripción de la categoría es obligatoria.',
            'descripcion_categoria.string' => 'La descripción de la categoría debe ser una cadena de texto.',
            'descripcion_categoria.max' => 'La descripción de la categoría no debe exceder los 100 caracteres.',
            'estado_categoria.required' => 'El estado de la categoría es obligatorio.',
            'estado_categoria.integer' => 'El estado de la categoría debe ser un valor numérico.',
            'estado_categoria.in' => 'El estado de la categoría debe ser 0 (inactivo) o 1 (activo).',
        ];
    }
}
