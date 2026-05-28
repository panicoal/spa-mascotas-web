<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Safe migration: only adds columns if they don't already exist.
     */
    public function up(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {

            if (!Schema::hasColumn('mascotas', 'tamanio')) {
                $table->string('tamanio', 20)->default('MEDIANO')->after('sexo');
            }

            if (!Schema::hasColumn('mascotas', 'edad')) {
                $table->unsignedTinyInteger('edad')->nullable()->after('tamanio');
            }

            if (!Schema::hasColumn('mascotas', 'unidad_edad')) {
                $table->string('unidad_edad', 10)->default('AÑOS')->nullable()->after('edad');
            }

            if (!Schema::hasColumn('mascotas', 'caracteristicas_fisicas')) {
                $table->text('caracteristicas_fisicas')->nullable()->after('color');
            }

            if (!Schema::hasColumn('mascotas', 'restricciones_medicas')) {
                $table->text('restricciones_medicas')->nullable()->after('caracteristicas_fisicas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->dropColumn([
                'tamanio',
                'edad',
                'unidad_edad',
                'caracteristicas_fisicas',
                'restricciones_medicas',
            ]);
        });
    }
};
