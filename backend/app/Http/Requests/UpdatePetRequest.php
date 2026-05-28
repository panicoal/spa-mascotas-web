<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'nombre' => [
                'sometimes',
                'string',
                'max:100'
            ],

            'especie' => [
                'sometimes',
                'in:PERRO,GATO,AVE,OTRO'
            ],

            'raza' => [
                'nullable',
                'string',
                'max:100'
            ],

            'sexo' => [
                'sometimes',
                'in:MACHO,HEMBRA'
            ],

            'tamanio' => [
                'sometimes',
                'in:PEQUEÑO,MEDIANO,GRANDE,GIGANTE'
            ],

            'edad' => [
                'nullable',
                'integer',
                'min:0',
                'max:99'
            ],

            'unidad_edad' => [
                'nullable',
                'in:MESES,AÑOS'
            ],

            'fecha_nacimiento' => [
                'nullable',
                'date'
            ],

            'peso' => [
                'nullable',
                'numeric',
                'min:0',
                'max:200'
            ],

            'color' => [
                'nullable',
                'string',
                'max:100'
            ],

            'caracteristicas_fisicas' => [
                'nullable',
                'string',
                'max:1000'
            ],

            'restricciones_medicas' => [
                'nullable',
                'string',
                'max:1000'
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:2000'
            ],

            'foto_url' => [
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }
}