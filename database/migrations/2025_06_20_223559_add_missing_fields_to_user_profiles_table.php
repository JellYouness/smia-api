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
        Schema::table('user_profiles', function (Blueprint $table) {
            // Personal information fields
            $table->string('phone_number')->nullable()->after('short_bio');
            $table->string('address')->nullable()->after('phone_number');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('postal_code')->nullable()->after('country');
            $table->string('profile_picture')->nullable()->after('postal_code');
            $table->date('date_of_birth')->nullable()->after('profile_picture');
            $table->enum('gender', ['MALE', 'FEMALE', 'OTHER'])->nullable()->after('date_of_birth');
            $table->string('preferred_language')->nullable()->after('gender');
            $table->string('timezone')->nullable()->after('preferred_language');

            // Privacy and preferences fields
            $table->json('privacy_settings')->nullable()->after('notification_preferences');
            $table->json('social_media_links')->nullable()->after('privacy_settings');
            $table->json('emergency_contact')->nullable()->after('social_media_links');
            $table->json('preferences')->nullable()->after('emergency_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number',
                'address',
                'city',
                'state',
                'country',
                'postal_code',
                'profile_picture',
                'date_of_birth',
                'gender',
                'preferred_language',
                'timezone',
                'privacy_settings',
                'social_media_links',
                'emergency_contact',
                'preferences',
            ]);
        });
    }
};
