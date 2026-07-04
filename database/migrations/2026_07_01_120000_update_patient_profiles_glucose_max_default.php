<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corrige el máximo de glucosa en ayunas por defecto: 180 (techo post-comida,
     * clínicamente incorrecto para ayunas) → 130 (meta ADA en ayunas). Alinea el
     * default de la BD con VitalSign::GLUCOSE_DEFAULT_MAX.
     */
    public function up(): void
    {
        Schema::table('patient_profiles', function (Blueprint $table) {
            $table->integer('target_glucose_max')->default(130)->change();
        });

        // Perfiles que conservan el viejo default 180 (sin personalización médica real).
        DB::table('patient_profiles')
            ->where('target_glucose_max', 180)
            ->update(['target_glucose_max' => 130]);
    }

    public function down(): void
    {
        Schema::table('patient_profiles', function (Blueprint $table) {
            $table->integer('target_glucose_max')->default(180)->change();
        });
    }
};
