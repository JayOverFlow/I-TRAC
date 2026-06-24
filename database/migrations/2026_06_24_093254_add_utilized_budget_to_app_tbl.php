<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('app_tbl', 'utilized_budget')) {
            Schema::table('app_tbl', function (Blueprint $table) {
                $table->decimal('utilized_budget', 12, 2)->nullable()->default(0.00)->after('app_total');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('app_tbl', 'utilized_budget')) {
            Schema::table('app_tbl', function (Blueprint $table) {
                $table->dropColumn('utilized_budget');
            });
        }
    }
};
