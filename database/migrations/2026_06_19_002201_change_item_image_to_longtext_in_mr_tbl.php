git<?php

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
        Schema::table('mr_tbl', function (Blueprint $table) {
            $table->longText('item_image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mr_tbl', function (Blueprint $table) {
            $table->string('item_image', 255)->nullable()->change();
        });
    }
};
