<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDetalleVentaRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'venta_id' => 'required|exists:ventas,id',
            'producto_uuid' => 'nullable|uuid',
            'descripcion_producto' => 'required|string|max:255',
            'cantidad' => 'required|numeric|min:0.01',
            'precio_unitario' => 'required|integer|min:0',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'subtotal_linea' => 'required|integer|min:1',
        ];
    }

    /**
     * Configure the validator instance with custom validations.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $cantidad = (float) ($this->input('cantidad') ?? 0);
            $precio = (int) ($this->input('precio_unitario') ?? 0);
            $descuento = (float) ($this->input('descuento_porcentaje') ?? 0);
            $subtotal = (int) ($this->input('subtotal_linea') ?? 0);

            // Validar que descuento no sea mayor a 100%
            if ($descuento > 100) {
                $validator->errors()->add('descuento_porcentaje', 'El descuento no puede exceder 100%.');
            }

            // Validar coherencia del subtotal: subtotal = (cantidad × precio) × (1 - descuento/100)
            $expectedSubtotal = (int) ($cantidad * $precio * (1 - ($descuento / 100)));

            // Permitir margen de tolerancia de ±1 por redondeos
            if (abs($subtotal - $expectedSubtotal) > 1) {
                $validator->errors()->add(
                    'subtotal_linea',
                    "Subtotal incorrecto. Esperado: $expectedSubtotal, recibido: $subtotal"
                );
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'venta_id.required' => 'La venta es obligatoria.',
            'venta_id.exists' => 'La venta seleccionada no existe.',
            'producto_uuid.uuid' => 'El UUID del producto debe ser válido.',
            'descripcion_producto.required' => 'La descripción del producto es obligatoria.',
            'descripcion_producto.max' => 'La descripción no debe exceder 255 caracteres.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.numeric' => 'La cantidad debe ser un número.',
            'cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'precio_unitario.required' => 'El precio unitario es obligatorio.',
            'precio_unitario.integer' => 'El precio unitario debe ser un número entero.',
            'precio_unitario.min' => 'El precio unitario no puede ser negativo.',
            'descuento_porcentaje.numeric' => 'El descuento debe ser un número.',
            'descuento_porcentaje.min' => 'El descuento no puede ser negativo.',
            'descuento_porcentaje.max' => 'El descuento no puede exceder 100.',
            'subtotal_linea.required' => 'El subtotal de la línea es obligatorio.',
            'subtotal_linea.integer' => 'El subtotal debe ser un número entero.',
            'subtotal_linea.min' => 'El subtotal debe ser mayor a 0.',
        ];
    }
}
