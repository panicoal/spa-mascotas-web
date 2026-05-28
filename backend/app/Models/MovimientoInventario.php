<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MovimientoInventario extends Model
{
    use HasUuids;

    protected $table = 'movimientos_inventario';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'producto_id',
        'usuario_id',
        'cita_id',
        'tipo_movimiento',
        'motivo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
    ];

    protected $casts = [
        'cantidad'       => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo'    => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}
