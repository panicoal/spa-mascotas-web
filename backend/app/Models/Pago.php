<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Pago extends Model
{
    use HasUuids;

    protected $table = 'pagos';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cita_id',
        'registrado_por',
        'metodo_pago',
        'monto',
        'monto_descuento',
        'monto_total',
        'referencia',
        'notas',
        'fecha_pago',
    ];

    protected $casts = [
        'monto'           => 'decimal:2',
        'monto_descuento' => 'decimal:2',
        'monto_total'     => 'decimal:2',
        'fecha_pago'      => 'datetime',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
