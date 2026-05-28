<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Pet extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'mascotas';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'cliente_id',
        'user_id',
        'nombre',
        'especie',
        'raza',
        'tamanio',
        'edad',
        'unidad_edad',
        'color',
        'peso_kg',
        'peso',
        'caracteristicas_fisicas',
        'restricciones_medicas',
        'fotos',
        'sexo',
        'fecha_nacimiento',
        'foto_url',
        'observaciones',
        'is_active'
    ];

    protected $attributes = [
        'tamanio' => 'MEDIANO',
    ];

    protected $appends = ['user_id', 'peso', 'observaciones'];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'peso_kg' => 'decimal:2',
            'is_active' => 'boolean',
            'fotos' => 'array'
        ];
    }

    // Accessors & Mutators for compatibility with legacy endpoints and frontend
    public function getUserIdAttribute()
    {
        return $this->attributes['cliente_id'] ?? null;
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['cliente_id'] = $value;
    }

    public function getPesoAttribute()
    {
        return $this->attributes['peso_kg'] ?? null;
    }

    public function setPesoAttribute($value)
    {
        $this->attributes['peso_kg'] = $value;
    }

    public function getObservacionesAttribute()
    {
        return $this->attributes['observaciones'] ?? null;
    }

    public function setObservacionesAttribute($value)
    {
        $this->attributes['observaciones'] = $value;
        $this->attributes['caracteristicas_fisicas'] = $value;
    }

    public function owner()
    {
        return $this->belongsTo(
            User::class,
            'cliente_id'
        );
    }
}