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
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // physical, nocturnal, neurological, atypical
            $table->timestamps();
        });

        Schema::create('symptom_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('symptom_id')->constrained()->onDelete('cascade');
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Revierte los cambios definidos por esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('symptom_user');
        Schema::dropIfExists('symptoms');
    }
};
