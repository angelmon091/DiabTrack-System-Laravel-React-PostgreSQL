<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('pending')->after('specialty')->index();
            $table->text('review_notes')->nullable()->after('approval_status');
            $table->foreignId('approved_by')->nullable()->after('review_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['approval_status']);
            $table->dropColumn(['approval_status', 'review_notes', 'approved_by', 'approved_at']);
        });
    }
};
