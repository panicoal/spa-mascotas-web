<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FichaGrooming extends Model
{
    use HasUuids;

    protected $table = 'fichas_grooming';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'cita_id',
        'groomer_id',
        'estado_ingreso_nudos',
        'estado_ingreso_pulgas',
        'estado_ingreso_heridas',
        'temperamento',
        'condicion_general',
        'recomendaciones_tecnicas',
        'tiempo_real_minutos',
        'fecha_apertura',
        'fecha_cierre',
        'checklist',
        'fotos'
    ];

    protected $casts = [
        'estado_ingreso_pulgas' => 'boolean',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'tiempo_real_minutos' => 'integer',
        'checklist' => 'array',
        'fotos' => 'array'
    ];

    public function appointment()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function groomer()
    {
        return $this->belongsTo(User::class, 'groomer_id');
    }
}
