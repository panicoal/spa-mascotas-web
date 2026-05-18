<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [

            'nombre_completo' => [
                'required',
                'string',
                'max:150'
            ],

            'email' => [
                'required',
                'email',
                'max:150',

                Rule::unique('usuarios', 'email')
                    ->ignore($userId)
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
            ],

            'rol' => [
                'required',
                'in:RECEPCION,GROOMER,ADMIN'
            ],

            'turno' => [
                'nullable',
                'string',
                'in:MAÑANA,TARDE,NOCHE'
            ],

            'password' => [
                'nullable',

                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ];
    }
}