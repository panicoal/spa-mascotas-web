<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BloqueoAgenda extends Model
{
    use HasUuids;

    protected $table = 'bloqueos_agenda';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'groomer_id',
        'fecha',
        'todo_el_dia',
        'hora_inicio',
        'hora_fin',
        'motivo',
        'created_by'
    ];

    protected $casts = [
        'fecha' => 'date',
        'todo_el_dia' => 'boolean'
    ];

    public function groomer()
    {
        return $this->belongsTo(User::class, 'groomer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
