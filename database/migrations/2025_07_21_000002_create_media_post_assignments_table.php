<?php

use App\Enums\CREATOR_PROJECT_PERMISSION;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('media_post_assignments', function (Blueprint $table) {
      $table->foreignId('post_id')->constrained('media_posts')->onDelete('cascade');
      $table->foreignId('creator_id')->constrained('creators')->onDelete('cascade');
      $table->enum('role', array_column(CREATOR_PROJECT_PERMISSION::cases(), 'value'))->default(CREATOR_PROJECT_PERMISSION::VIEWER->value);
      $table->timestamp('assigned_at')->useCurrent();

      $table->timestamps();

      $table->primary(['post_id', 'creator_id']);
      $table->index('creator_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('media_post_assignments');
  }
};
