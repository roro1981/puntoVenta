<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorporateDataRequest extends FormRequest
{
    
    public function rules(): array
    {
        return [
            'description_item' => 'required|string|max:255'
        ];
    }
}
