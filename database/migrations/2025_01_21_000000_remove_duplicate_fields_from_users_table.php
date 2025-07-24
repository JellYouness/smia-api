<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove fields if they exist
            $columnsToRemove = [
                'language',
                'notification_email',
                'notification_sms',
                'notification_push',
                'notification_in_app',
                'privacy',
                'profile_image',
                'preferred_language',
                'timezone'
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-add the fields if they were removed
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language')->nullable();
            }
            if (!Schema::hasColumn('users', 'notification_email')) {
                $table->boolean('notification_email')->default(true);
            }
            if (!Schema::hasColumn('users', 'notification_sms')) {
                $table->boolean('notification_sms')->default(false);
            }
            if (!Schema::hasColumn('users', 'notification_push')) {
                $table->boolean('notification_push')->default(true);
            }
            if (!Schema::hasColumn('users', 'notification_in_app')) {
                $table->boolean('notification_in_app')->default(true);
            }
            if (!Schema::hasColumn('users', 'privacy')) {
                $table->string('privacy')->default('PUBLIC');
            }
            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable();
            }
            if (!Schema::hasColumn('users', 'preferred_language')) {
                $table->enum('preferred_language', ['ENGLISH', 'FRENCH', 'ARABIC', 'SPANISH'])->nullable();
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->nullable();
            }
        });
    }
};
