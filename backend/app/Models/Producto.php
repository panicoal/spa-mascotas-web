<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Producto extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'productos';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'categoria',
        'unidad_medida',
        'precio_compra',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'activo',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta'  => 'decimal:2',
        'stock_actual'  => 'integer',
        'stock_minimo'  => 'integer',
        'activo'        => 'boolean',
    ];

    protected $appends = ['bajo_stock'];

    public function getBajoStockAttribute(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }
}
