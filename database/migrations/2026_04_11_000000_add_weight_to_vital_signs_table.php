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
        Schema::table('vital_signs', function (Blueprint $row) {
            $row->decimal('weight', 5, 2)->nullable()->after('heart_rate');
        });
    }

    /**
     * Revierte los cambios definidos por esta migración.
     */
    public function down(): void
    {
        Schema::table('vital_signs', function (Blueprint $row) {
            $row->dropColumn('weight');
        });
    }
};
