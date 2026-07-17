<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aplica los cambios definidos por esta migración.
     * Optimize common health queries with indexing.
     */
    public function up(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('nutrition_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });

        // Symptom tracking is pivot, index important for fast aggregation
        if (Schema::hasTable('symptom_user')) {
            Schema::table('symptom_user', function (Blueprint $table) {
                $table->index(['user_id', 'logged_at']);
            });
        }
    }

    /**
     * Revierte los cambios definidos por esta migración.
     */
    public function down(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('nutrition_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        if (Schema::hasTable('symptom_user')) {
            Schema::table('symptom_user', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'logged_at']);
            });
        }
    }
};
