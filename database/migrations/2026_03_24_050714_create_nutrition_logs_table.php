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
        Schema::create('nutrition_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('meal_type'); // breakfast, lunch, dinner, snack, correction
            $table->integer('carbs_grams')->nullable();
            $table->time('consumed_at')->nullable();
            $table->json('food_categories')->nullable();
            $table->string('medication_taken')->nullable();
            $table->string('medication_dose')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte los cambios definidos por esta migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_logs');
    }
};
