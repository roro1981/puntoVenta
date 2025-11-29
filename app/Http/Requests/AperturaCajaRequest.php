<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AperturaCajaRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'monto_inicial' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Mensajes de validación personalizados
     */
    public function messages(): array
    {
        return [
            'monto_inicial.required' => 'El monto inicial es obligatorio',
            'monto_inicial.numeric' => 'El monto inicial debe ser un número',
            'monto_inicial.min' => 'El monto inicial no puede ser negativo',
            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres'
        ];
    }
}
