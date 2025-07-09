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
        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Skills as JSON array
            $table->json('skills');

            $table->enum('verification_status', ['UNVERIFIED', 'PENDING', 'VERIFIED', 'FEATURED'])->default('UNVERIFIED');

            // Portfolio as JSON array of objects
            $table->json('portfolio')->nullable();

            $table->integer('experience')->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->enum('availability', ['AVAILABLE', 'LIMITED', 'UNAVAILABLE', 'BUSY'])->default('AVAILABLE');

            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('rating_count')->default(0);

            // Regional expertise as JSON array of objects
            $table->json('regional_expertise');

            // Languages as JSON array of objects
            $table->json('languages');

            $table->boolean('is_journalist')->default(false);

            // Media types as JSON array of enums
            $table->json('media_types');

            // Certifications as JSON array of objects
            $table->json('certifications')->nullable();

            // Equipment info as JSON object
            $table->json('equipment_info')->nullable();

            $table->timestamps();

            // Add indexes
            $table->index('verification_status');
            $table->index('availability');
            $table->index('average_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creators');
    }
};
