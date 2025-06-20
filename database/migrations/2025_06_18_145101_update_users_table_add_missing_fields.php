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
            // Add new columns
            $table->string('username')->nullable()->after('email');
            $table->string('first_name')->nullable()->after('username');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('profile_image')->nullable()->after('last_name');
            $table->string('phone_number')->nullable()->after('profile_image');
            $table->timestamp('date_registered')->useCurrent()->after('phone_number');
            $table->timestamp('last_login')->nullable()->after('date_registered');
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'PENDING', 'SUSPENDED', 'DELETED'])->default('PENDING')->after('last_login');
            $table->enum('user_type', ['CLIENT', 'CREATOR', 'AMBASSADOR', 'ADMIN'])->default('CLIENT')->after('status');
            $table->boolean('two_factor_enabled')->default(false)->after('user_type');
            $table->boolean('accepted_terms')->default(false)->after('two_factor_enabled');
            $table->boolean('email_verified')->default(false)->after('accepted_terms');
            $table->enum('preferred_language', ['ENGLISH', 'FRENCH', 'ARABIC', 'SPANISH'])->nullable()->after('email_verified');
            $table->string('timezone')->nullable()->after('preferred_language');
        });

        // Generate usernames for existing users
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $username = strtolower(explode('@', $user->email)[0]);
            $baseUsername = $username;
            $counter = 1;

            // Ensure username uniqueness
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $username]);
        }

        // Now make username unique and not nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable(false)->change();
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn([
                'username',
                'first_name',
                'last_name',
                'profile_image',
                'phone_number',
                'date_registered',
                'last_login',
                'status',
                'user_type',
                'two_factor_enabled',
                'accepted_terms',
                'email_verified',
                'preferred_language',
                'timezone'
            ]);
        });
    }
};
