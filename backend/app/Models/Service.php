<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Service extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'servicios';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'precio_base',
        'duracion_base_minutos',
        'incremento_pequenio',
        'incremento_mediano',
        'incremento_grande',
        'categoria',
        'activo'
    ];

    protected $appends = ['precio', 'duracion_minutos', 'is_active'];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
        'duracion_base_minutos' => 'integer'
    ];

    // Accessors & Mutators for compatibility
    public function getPrecioAttribute()
    {
        return $this->attributes['precio_base'] ?? null;
    }

    public function setPrecioAttribute($value)
    {
        $this->attributes['precio_base'] = $value;
    }

    public function getDuracionMinutosAttribute()
    {
        return $this->attributes['duracion_base_minutos'] ?? null;
    }

    public function setDuracionMinutosAttribute($value)
    {
        $this->attributes['duracion_base_minutos'] = $value;
    }

    public function getIsActiveAttribute()
    {
        return $this->attributes['activo'] ?? true;
    }

    public function setIsActiveAttribute($value)
    {
        $this->attributes['activo'] = $value;
    }
}