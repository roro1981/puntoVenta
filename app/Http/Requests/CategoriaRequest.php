<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaRequest extends FormRequest
{
    
    public function rules(): array
    {
        return [
            'descripcion_categoria' => 'required|string|max:100|unique:categorias'
        ];
    }

    public function messages()
    {
        return [
            'descripcion_categoria.required' => 'La descripción de la categoría es obligatoria.',
            'descripcion_categoria.string' => 'La descripción de la categoría debe ser una cadena de texto.',
            'descripcion_categoria.max' => 'La descripción de la categoría no debe exceder los 100 caracteres.',
            'descripcion_categoria.unique' => 'La categoría ya existe.',
        ];
    }
}
