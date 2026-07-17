<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('daily_tips')->update(['status' => 'approved']);

        if (Schema::hasColumn('daily_tips', 'reviewed_by')) {
            Schema::table('daily_tips', function (Blueprint $table) {
                $table->dropForeign(['reviewed_by']);
                $table->dropColumn('reviewed_by');
            });
        }

        if (Schema::hasColumn('daily_tips', 'rejection_reason')) {
            Schema::table('daily_tips', function (Blueprint $table) {
                $table->dropColumn('rejection_reason');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE daily_tips MODIFY status ENUM('approved') NOT NULL DEFAULT 'approved'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE daily_tips MODIFY status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('daily_tips', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason')->nullable();
        });
    }
};
