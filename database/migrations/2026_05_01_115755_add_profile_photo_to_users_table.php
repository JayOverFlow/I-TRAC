<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds the user_profile_photo column to store the relative path
     * of the user's uploaded avatar (stored in public/img/profiles/).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_profile_photo')->nullable()->after('user_contactno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_profile_photo');
        });
    }
};
