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
        Schema::create('team_member_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambassador_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable(); // Optional role within the team
            $table->boolean('is_primary')->default(false); // Indicates if this will be the primary team member
            $table->enum('status', ['PENDING', 'ACCEPTED', 'DECLINED', 'EXPIRED'])->default('PENDING');
            $table->string('token')->unique(); // Unique token for invitation link
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index(['ambassador_id', 'user_id']);
            $table->index('status');
            $table->index('token');
            $table->index('expires_at');

            // Ensure unique combination of ambassador and user for pending invitations
            $table->unique(['ambassador_id', 'user_id', 'status'], 'unique_pending_invitation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_member_invitations');
    }
};
