<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds HackClub OAuth columns to the users table.
     * GitHub columns (github_id, github_token, github_refresh_token)
     * were already added in the original users table migration.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('hackclub_id')->nullable()->after('github_refresh_token');
            $table->string('hackclub_token')->nullable()->after('hackclub_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['hackclub_id', 'hackclub_token']);
        });
    }
};
