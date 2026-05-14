<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    
    public function rules(): array
    {
        if ($this->is('login')) {
            return [
                'name' => 'required|string|max:50' ,
                'password' => "required|string|min:6|max:128"
            ];
        } elseif ($this->isMethod('PUT')) {
            $userUuid = (string) $this->route('uuid');

            return [
                'name_complete_edit' => "required|string|max:255",
                'email_edit' => "required|email|max:255|unique:users,email," . $userUuid . ",uuid",
                'password_edit' => "nullable|string|min:6|max:128",
                'role_id_edit' => "required|integer"
            ];
        } else {
            return [
                'name' => 'required|string|max:50|unique:users' ,
                'name_complete' => "required|string|max:255|unique:users",
                'email' => "required|email|max:255|unique:users,email",
                'password' => "required|string|min:6|max:128",
                'role_id' => "required|integer"
            ];
        }
    }
}