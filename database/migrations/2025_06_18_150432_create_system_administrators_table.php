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
        Schema::create('system_administrators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->enum('access_level', ['STANDARD', 'ELEVATED', 'SUPER'])->default('STANDARD');

            // Admin permissions as JSON object
            $table->json('admin_permissions');

            // Departments as JSON array of enums
            $table->json('departments')->nullable();

            $table->boolean('audit_log')->default(true);
            $table->timestamp('last_permission_update')->nullable();

            $table->boolean('restricted_ip_access')->default(false);

            // Allowed IP addresses as JSON array
            $table->json('allowed_ip_addresses')->nullable();

            $table->string('emergency_contact')->nullable();
            $table->enum('security_clearance', ['BASIC', 'SENSITIVE', 'CONFIDENTIAL'])->nullable();

            $table->timestamps();

            // Add indexes
            $table->index('access_level');
            $table->index('security_clearance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_administrators');
    }
};
