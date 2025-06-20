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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->enum('company_size', ['INDIVIDUAL', 'SMALL', 'MEDIUM', 'LARGE', 'ENTERPRISE'])->nullable();
            $table->enum('industry', ['MEDIA', 'EDUCATION', 'HEALTHCARE', 'TECHNOLOGY', 'FINANCE', 'ENTERTAINMENT', 'OTHER'])->nullable();
            $table->string('website_url')->nullable();

            // Billing address fields
            $table->string('billing_street')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_country')->nullable();

            $table->string('tax_identifier')->nullable();
            $table->enum('budget', ['SMALL', 'MEDIUM', 'LARGE', 'ENTERPRISE'])->nullable();

            // Store preferred creators as JSON array of user IDs
            $table->json('preferred_creators')->nullable();

            $table->integer('project_count')->default(0);

            // Store default project settings as JSON
            $table->json('default_project_settings')->nullable();

            $table->timestamps();

            // Add indexes
            $table->index('company_name');
            $table->index('industry');
            $table->index('company_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
