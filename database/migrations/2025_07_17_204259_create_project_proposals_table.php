<?php

use App\Enums\PROJECT_PROPOSAL_STATUS;
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
    Schema::create('project_proposals', function (Blueprint $table) {
      $table->id();

      $table->foreignId('invite_id')->constrained('project_invites')->cascadeOnDelete();
      $table->unique('invite_id');

      $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
      $table->foreignId('creator_id')->constrained('creators')->cascadeOnDelete();

      $table->decimal('amount', 12, 2)->nullable();
      $table->char('currency', 3)->default('USD');
      $table->integer('duration_days')->nullable();
      $table->text('cover_letter')->nullable();
      $table->json('attachments')->nullable();
      $table->enum('status', array_map(fn($s) => $s->value, PROJECT_PROPOSAL_STATUS::cases()))->default(PROJECT_PROPOSAL_STATUS::PENDING->value);
      $table->json('meta')->nullable();
      $table->timestamps();

      $table->index(['project_id', 'status']);
      $table->index('creator_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('project_proposals');
  }
};
