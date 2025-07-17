<?php

use App\Enums\PROJECT_INVITE_STATUS;
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
    Schema::create('project_invites', function (Blueprint $table) {
      $table->id();
      $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
      $table->foreignId('creator_id')->constrained('creators')->cascadeOnDelete();
      $table->text('message')->nullable();
      $table->enum('status', array_map(fn($status) => $status->value, PROJECT_INVITE_STATUS::cases()))
        ->default(PROJECT_INVITE_STATUS::PENDING->value);
      $table->timestamp('expires_at')->nullable();
      $table->timestamp('accepted_at')->nullable();
      $table->timestamp('declined_at')->nullable();
      $table->timestamps();

      $table->unique(['project_id', 'creator_id']);
      $table->index('status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('project_invites');
  }
};
