<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cita extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'citas';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'cliente_id',
        'mascota_id',
        'servicio_id',
        'groomer_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'motivo_cancelacion',
        'calificacion_promedio',
        'creada_por'
    ];

    protected $casts = [
        'fecha' => 'date',
        'calificacion_promedio' => 'decimal:1'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'mascota_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'servicio_id');
    }

    public function groomer()
    {
        return $this->belongsTo(User::class, 'groomer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

    public function groomingCard()
    {
        return $this->hasOne(FichaGrooming::class, 'cita_id');
    }
}
