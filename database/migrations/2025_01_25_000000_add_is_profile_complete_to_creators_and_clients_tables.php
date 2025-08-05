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
        Schema::table('creators', function (Blueprint $table) {
            $table->boolean('is_profile_complete')->default(false)->after('reviewed_by');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('is_profile_complete')->default(false)->after('default_project_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->dropColumn('is_profile_complete');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('is_profile_complete');
        });
    }
};
