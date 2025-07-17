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
    Schema::create('proposal_comments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('proposal_id')->constrained('project_proposals')->cascadeOnDelete();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('parent_id')->nullable()->constrained('proposal_comments')->nullOnDelete();
      $table->text('body');
      $table->json('attachments')->nullable();
      $table->timestamp('read_at')->nullable();

      $table->timestamps();

      $table->index('proposal_id');
      $table->index('parent_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('proposal_comments');
  }
};
