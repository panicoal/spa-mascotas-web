<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
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
                'max:150'
            ],

            'email' => [
                'required',
                'email',
                'max:150',
                'unique:usuarios,email'
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
            ]
        ];
    }

    public function messages(): array
    {
        return [

            'email.unique' =>
                'El correo ya está registrado.',

            'rol.in' =>
                'El rol seleccionado no es válido.',
        ];
    }
}