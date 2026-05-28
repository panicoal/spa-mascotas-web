<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pagos')) {
            Schema::create('pagos', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('cita_id')->constrained('citas')->cascadeOnDelete();
                $table->foreignUuid('registrado_por')->constrained('usuarios')->cascadeOnDelete();
                $table->string('metodo_pago', 30); // QR, EFECTIVO, TRANSFERENCIA
                $table->decimal('monto', 10, 2);
                $table->decimal('monto_descuento', 10, 2)->default(0);
                $table->decimal('monto_total', 10, 2);
                $table->string('referencia', 100)->nullable(); // Nro. transferencia, código QR, etc.
                $table->text('notas')->nullable();
                $table->timestamp('fecha_pago')->useCurrent();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
