<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HorarioGroomer extends Model
{
    use HasUuids;

    protected $table = 'horario_trabajo_groomer';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'groomer_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'pausa_inicio',
        'pausa_fin'
    ];

    public function groomer()
    {
        return $this->belongsTo(User::class, 'groomer_id');
    }
}
