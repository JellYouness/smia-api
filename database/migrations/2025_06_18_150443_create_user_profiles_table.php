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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->text('bio')->nullable();
            $table->string('short_bio', 200)->nullable();

            // Social links as JSON object
            $table->json('social_links')->nullable();

            // Education as JSON array of objects
            $table->json('education')->nullable();

            // Professional background as JSON array of objects
            $table->json('professional_background')->nullable();

            // Achievements as JSON array
            $table->json('achievements')->nullable();

            $table->enum('profile_visibility', ['PUBLIC', 'PRIVATE', 'TEAM_ONLY'])->default('PUBLIC');
            $table->integer('profile_completeness')->default(0);
            $table->timestamp('last_updated')->useCurrent();

            $table->string('cover_image')->nullable();
            $table->string('display_name')->nullable();

            // Notification preferences as JSON object
            $table->json('notification_preferences')->nullable();

            $table->string('audio_introduction')->nullable();

            $table->timestamps();

            // Add indexes
            $table->index('profile_visibility');
            $table->index('profile_completeness');
            $table->index('display_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
