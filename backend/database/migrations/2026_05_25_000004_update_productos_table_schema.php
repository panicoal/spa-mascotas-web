<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'codigo')) {
                $table->string('codigo', 50)->unique()->nullable();
            }
            if (!Schema::hasColumn('productos', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }
            if (Schema::hasColumn('productos', 'costo') && !Schema::hasColumn('productos', 'precio_compra')) {
                $table->renameColumn('costo', 'precio_compra');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'precio_compra') && !Schema::hasColumn('productos', 'costo')) {
                $table->renameColumn('precio_compra', 'costo');
            }
            if (Schema::hasColumn('productos', 'codigo')) {
                $table->dropColumn('codigo');
            }
            if (Schema::hasColumn('productos', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
        });
    }
};
