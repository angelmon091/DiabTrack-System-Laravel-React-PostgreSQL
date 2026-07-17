<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aplica los cambios definidos por esta migración.
     */
    public function up(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->string('stress_level')->nullable()->after('hba1c');
            $table->text('notes')->nullable()->after('stress_level');
        });
    }

    /**
     * Revierte los cambios definidos por esta migración.
     */
    public function down(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->dropColumn(['stress_level', 'notes']);
        });
    }
};
