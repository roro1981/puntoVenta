<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GlobalsRequest extends FormRequest
{
    
    public function rules(): array
    {
        return [
            'valor_var' => "required|string|max:50"
        ];
    }
}
