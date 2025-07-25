<?php

use App\Enums\VERSION_STATUS;
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
    Schema::create('media_post_versions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('post_id')->constrained('media_posts')->cascadeOnDelete();
      $table->unsignedInteger('number');
      $table->enum('status', array_map(fn($status) => $status->value, VERSION_STATUS::cases()))->default(VERSION_STATUS::IN_REVIEW->value);
      $table->foreignId('created_by')->nullable()
        ->constrained('users')->nullOnDelete();
      $table->timestamps();

      $table->unique(['post_id', 'number']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('media_post_versions');
  }
};
