<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class StoreVentaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero_venta' => 'nullable|string|max:50|unique:ventas,numero_venta',
            'total' => 'required|integer|min:1',
            'total_descuentos' => 'nullable|integer|min:0',
            'user_id' => 'nullable|exists:users,id',
            'forma_pago' => 'required|string|max:100',
            'estado' => 'nullable|string|max:50|in:completada,anulada,pendiente',
            'fecha_venta' => 'nullable|date_format:Y-m-d H:i:s',
            'detalles' => 'required|array|min:1',
            'detalles.*.venta_id' => 'nullable|integer',
            'detalles.*.producto_uuid' => 'nullable|uuid',
            'detalles.*.descripcion_producto' => 'required|string|max:255',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|integer|min:0',
            'detalles.*.descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'detalles.*.subtotal_linea' => 'required|integer|min:0',
            'formas_pago_desglose' => 'nullable|array',
            'formas_pago_desglose.*.forma' => 'required_with:formas_pago_desglose|string|max:50',
            'formas_pago_desglose.*.monto' => 'required_with:formas_pago_desglose|integer|min:1',
        ];
    }

    /**
     * Configure the validator instance with custom validations.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Validar que total sea mayor a 0
            if ($this->input('total') <= 0) {
                $validator->errors()->add('total', 'El total debe ser mayor a 0.');
            }

            // Validar que total_descuentos no sea mayor al total
            $totalDescuentos = (int) ($this->input('total_descuentos') ?? 0);
            if ($totalDescuentos >= $this->input('total')) {
                $validator->errors()->add('total_descuentos', 'El total de descuentos no puede ser mayor o igual al total.');
            }

            // Validar que haya al menos 1 detalle
            $detalles = $this->input('detalles', []);
            if (empty($detalles)) {
                $validator->errors()->add('detalles', 'La venta debe tener al menos 1 producto.');
            }

            // Validar coherencia de subtotales
            $sumaSubtotales = 0;
            foreach ($detalles as $idx => $detalle) {
                $cantidad = (float) ($detalle['cantidad'] ?? 0);
                $precio = (int) ($detalle['precio_unitario'] ?? 0);
                $descuento = (float) ($detalle['descuento_porcentaje'] ?? 0);
                $subtotal = (int) ($detalle['subtotal_linea'] ?? 0);

                // Calcular subtotal esperado
                $expectedSubtotal = (int) ($cantidad * $precio * (1 - ($descuento / 100)));

                // Permitir margen de tolerancia de ±1 por redondeos
                if (abs($subtotal - $expectedSubtotal) > 1) {
                    $validator->errors()->add(
                        "detalles.{$idx}.subtotal_linea",
                        "Subtotal incorrecto en línea " . ($idx + 1) . ". Esperado: $expectedSubtotal, recibido: $subtotal"
                    );
                }
                $sumaSubtotales += $subtotal;
            }

            // Validar que suma de subtotales coincida con total
            // Los subtotales ya vienen con descuentos aplicados por línea
            $totalEsperado = $sumaSubtotales;
            $totalRecibido = (int) $this->input('total');
            
            if (abs($totalRecibido - $totalEsperado) > 1) {
                $validator->errors()->add(
                    'total',
                    "Total incorrecto. Esperado: $totalEsperado, recibido: $totalRecibido"
                );
            }

            // Validar que fecha_venta no sea en el futuro
            if ($this->input('fecha_venta')) {
                $fechaVenta = Carbon::createFromFormat('Y-m-d H:i:s', $this->input('fecha_venta'));
                if ($fechaVenta->isFuture()) {
                    $validator->errors()->add('fecha_venta', 'La fecha de venta no puede ser en el futuro.');
                }
            }

            // Validar pago MIXTO
            if ($this->input('forma_pago') === 'MIXTO') {
                $formasPagoDesglose = $this->input('formas_pago_desglose', []);
                
                if (empty($formasPagoDesglose)) {
                    $validator->errors()->add('formas_pago_desglose', 'El pago MIXTO debe tener al menos una forma de pago en el desglose.');
                } else {
                    // Validar que la suma de los montos sea igual al total
                    $sumaMontosDesglose = array_reduce($formasPagoDesglose, function($sum, $fp) {
                        return $sum + (int)($fp['monto'] ?? 0);
                    }, 0);

                    $totalVenta = (int) $this->input('total');
                    
                    if (abs($sumaMontosDesglose - $totalVenta) > 1) {
                        $validator->errors()->add(
                            'formas_pago_desglose',
                            "La suma de los montos del desglose ($sumaMontosDesglose) debe ser igual al total de la venta ($totalVenta)."
                        );
                    }
                }
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'numero_venta.unique' => 'El número de venta ya existe.',
            'numero_venta.max' => 'El número de venta no debe exceder 50 caracteres.',
            'total.required' => 'El total es obligatorio.',
            'total.integer' => 'El total debe ser un número entero.',
            'total.min' => 'El total debe ser mayor a 0.',
            'total_descuentos.min' => 'El total de descuentos no puede ser negativo.',
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'forma_pago.required' => 'La forma de pago es obligatoria.',
            'forma_pago.max' => 'La forma de pago no debe exceder 100 caracteres.',
            'estado.in' => 'El estado debe ser: completada, anulada o pendiente.',
            'fecha_venta.date_format' => 'El formato de fecha debe ser Y-m-d H:i:s.',
            'detalles.required' => 'La venta debe tener al menos 1 producto.',
            'detalles.array' => 'Los detalles deben ser un array.',
            'detalles.min' => 'La venta debe tener al menos 1 producto.',
            'detalles.*.descripcion_producto.required' => 'La descripción del producto es obligatoria en cada línea.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria en cada línea.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a 0 en cada línea.',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio en cada línea.',
            'detalles.*.subtotal_linea.required' => 'El subtotal es obligatorio en cada línea.',
        ];
    }
}
