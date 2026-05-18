<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'nombre_completo' => [
                'required',
                'string',
                'min:5',
                'max:150'
            ],

            'email' => [
                'required',
                'email',
                'max:150',
                'unique:usuarios,email'
            ],

            'password' => [
                'required',
                'confirmed',
                'min:8',

                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/'
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:20'
            ],

            'ci' => [
                'nullable',
                'string',
                'max:30'
            ]
        ];
    }
    public function messages(): array
    {
        return [

            'password.regex' =>
                'La contraseña debe contener mayúsculas, minúsculas, números y símbolos.',

            'password.confirmed' =>
                'Las contraseñas no coinciden.'
        ];
    }
}
