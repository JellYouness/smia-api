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
        Schema::create('ambassador_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambassador_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable(); // Optional role within the team
            $table->boolean('is_primary')->default(false); // Indicates if this is the primary team member
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            // Add indexes
            $table->index(['ambassador_id', 'user_id']);
            $table->index('is_primary');

            // Ensure unique combination of ambassador and user
            $table->unique(['ambassador_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ambassador_team_members');
    }
};
