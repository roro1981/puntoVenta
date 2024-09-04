<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImpuestosRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'valor_imp' => "required|regex:/^\d{1,3}\.\d{1}$/"
        ];
    }
}
