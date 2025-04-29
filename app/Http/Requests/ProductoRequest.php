<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductoRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'codigo' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string', 'max:255'],
            'precio_compra_neto' => ['required', 'integer', 'min:0'],
            'impuesto_1' => ['required', 'numeric', 'min:1', 'max:99.9'],
            'impuesto_2' => ['nullable', 'numeric', 'min:1', 'max:99.9'],
            'precio_compra_bruto' => ['required', 'integer', 'min:1'],
            'precio_venta' => ['required', 'integer', 'min:1'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0', 'max:999.9'],
            'categoria' => ['required', 'exists:categorias,id'],
            'unidad_medida' => ['required', 'in:UN,L,KG,CJ'],
            'tipo' => ['required', 'in:P,S,I,PR,R'],
            'nom_foto' => ['nullable', 'string', 'max:255']
        ];

        if ($this->isMethod('POST')) {
            $rules['codigo'][] = Rule::unique('productos', 'codigo');
            $rules['descripcion'][] = Rule::unique('productos', 'descripcion');
        }

        if ($this->isMethod('PUT')) {
            $rules['codigo'] = ['string', 'max:100'];
            $rules['descripcion'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'codigo.required' => 'El código es obligatorio',
            'codigo.unique' => 'Codigo de producto ya existe',
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.unique' => 'Descripcion de producto ya existe',
            'precio_compra_neto.required' => 'El precio de compra neto es obligatorio',
            'impuesto_1.required' => 'El impuesto 1 es obligatorio.',
            'impuesto_1.numeric' => 'El impuesto 1 debe ser numérico.',
            'impuesto_1.min' => 'El impuesto 1 debe ser al menos 1.',
            'impuesto_1.max' => 'El impuesto 1 no puede superar 99.9.',
            'impuesto_2.numeric' => 'El impuesto 2 debe ser numérico.',
            'impuesto_2.min' => 'El impuesto 2 debe ser al menos 1.',
            'impuesto_2.max' => 'El impuesto 2 no puede superar 99.9.',
            'precio_compra_bruto.required' => 'El precio de compra bruto es obligatorio.',
            'precio_compra_bruto.integer' => 'El precio de compra bruto debe ser numérico.',
            'precio_compra_bruto.min' => 'El precio de compra bruto debe ser al menos 1.',
            'precio_venta.required' => 'El precio de venta público es obligatorio.',
            'precio_venta.integer' => 'El precio de venta público debe ser numérico.',
            'precio_venta.min' => 'El precio de venta público debe ser al menos 1.',
            'categoria.required' => 'La categoría es obligatoria.',
            'categoria.exists' => 'Categoria no existe.',
            'tipo.required' => 'El tipo de producto es obligatorio.',
            'tipo.in' => 'El tipo de producto debe ser uno de los siguientes: PRODUCTO, NO AFECTO A STOCK, INSUMO.',
            'nom_foto.max' => 'El nombre de la foto no puede sobrepasar los 255 caracteres',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $codigo = $this->input('codigo');
            $descripcion = $this->input('descripcion');

            if ($this->isMethod('POST')) {
                $existeEnRecetas = DB::table('recetas')->where('codigo', $codigo)->exists();
                $existeEnPromociones = DB::table('promociones')->where('codigo', $codigo)->exists();

                if ($existeEnRecetas || $existeEnPromociones) {
                    $validator->errors()->add('codigo', 'El código ya existe en recetas o promociones.');
                }

                $existeEnRecetasDesc = DB::table('recetas')->where('nombre', $descripcion)->exists();
                $existeEnPromocionesDesc = DB::table('promociones')->where('nombre', $descripcion)->exists();

                if ($existeEnRecetasDesc || $existeEnPromocionesDesc) {
                    $validator->errors()->add('descripcion', 'La descripción ya existe en recetas o promociones.');
                }
            }
        });
    }
}
