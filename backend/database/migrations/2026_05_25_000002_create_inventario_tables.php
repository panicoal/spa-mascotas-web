<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de productos
        if (!Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nombre', 150);
                $table->string('codigo', 50)->unique()->nullable();
                $table->text('descripcion')->nullable();
                $table->string('categoria', 80)->default('GENERAL');
                $table->string('unidad_medida', 30)->default('UNIDAD'); // UNIDAD, ML, KG, LITRO
                $table->decimal('precio_compra', 10, 2)->nullable();
                $table->decimal('precio_venta', 10, 2)->nullable();
                $table->integer('stock_actual')->default(0);
                $table->integer('stock_minimo')->default(5);  // Alert threshold
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Tabla de movimientos de inventario
        if (!Schema::hasTable('movimientos_inventario')) {
            Schema::create('movimientos_inventario', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('producto_id')->constrained('productos')->cascadeOnDelete();
                $table->foreignUuid('usuario_id')->constrained('usuarios')->cascadeOnDelete();
                $table->foreignUuid('cita_id')->nullable()->constrained('citas')->nullOnDelete();
                $table->string('tipo_movimiento', 20); // ENTRADA, SALIDA, AJUSTE
                $table->string('motivo', 200)->nullable();
                $table->integer('cantidad');
                $table->integer('stock_anterior');
                $table->integer('stock_nuevo');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('productos');
    }
};
