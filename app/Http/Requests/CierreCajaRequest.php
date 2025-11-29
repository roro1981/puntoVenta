<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CierreCajaRequest extends FormRequest
{
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'monto_final_declarado' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Mensajes de validación personalizados
     */
    public function messages(): array
    {
        return [
            'monto_final_declarado.required' => 'El monto final es obligatorio',
            'monto_final_declarado.numeric' => 'El monto final debe ser un número',
            'monto_final_declarado.min' => 'El monto final no puede ser negativo',
            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres'
        ];
    }
}
