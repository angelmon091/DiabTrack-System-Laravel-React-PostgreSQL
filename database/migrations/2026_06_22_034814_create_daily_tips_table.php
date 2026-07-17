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
        Schema::create('daily_tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // El paciente
            $table->text('tip_text'); // Texto sugerido por la IA
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // Médico o Cuidador
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte los cambios definidos por esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_tips');
    }
};
