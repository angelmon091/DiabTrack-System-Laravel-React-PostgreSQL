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
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('glucose_level')->nullable();
            $table->integer('systolic')->nullable();
            $table->integer('diastolic')->nullable();
            $table->integer('heart_rate')->nullable();
            $table->decimal('hba1c', 4, 2)->nullable();
            $table->string('measurement_moment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte los cambios definidos por esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
