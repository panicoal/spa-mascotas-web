<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'nombre' => [
                'required',
                'string',
                'max:150'
            ],

            'descripcion' => [
                'nullable',
                'string'
            ],

            'precio' => [
                'required',
                'numeric',
                'min:0'
            ],

            'duracion_minutos' => [
                'required',
                'integer',
                'min:1'
            ],

            'categoria' => [
                'required',
                'in:GROOMING,SPA,SALUD,VACUNAS,ESTETICA'
            ],

            'imagen_url' => [
                'nullable',
                'string'
            ],

            'is_active' => [
                'boolean'
            ]
        ];
    }
}