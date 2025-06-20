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
        Schema::create('ambassadors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Team members as JSON array of user IDs
            $table->json('team_members');

            $table->string('team_name');

            // Specializations as JSON array
            $table->json('specializations');

            // Regional expertise as JSON array of objects
            $table->json('regional_expertise');

            // Service offerings as JSON array
            $table->json('service_offerings');

            $table->integer('client_count')->default(0);
            $table->integer('project_capacity')->nullable();

            $table->enum('application_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->timestamp('application_date')->useCurrent();

            // Verification documents as JSON array of URLs
            $table->json('verification_documents')->nullable();

            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->text('team_description');

            // Featured work as JSON array of objects
            $table->json('featured_work')->nullable();

            $table->integer('years_in_business')->nullable();

            // Business address fields
            $table->string('business_street')->nullable();
            $table->string('business_city')->nullable();
            $table->string('business_state')->nullable();
            $table->string('business_postal_code')->nullable();
            $table->string('business_country')->nullable();

            $table->timestamps();

            // Add indexes
            $table->index('application_status');
            $table->index('team_name');
            $table->index('client_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ambassadors');
    }
};
