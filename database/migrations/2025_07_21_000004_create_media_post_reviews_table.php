<?php

use App\Enums\MEDIA_POST_REVIEW_DECISION;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('media_post_reviews', function (Blueprint $table) {
      $table->id();
      $table->foreignId('post_id')->constrained('media_posts')->onDelete('cascade');
      $table->morphs('reviewer');
      $table->enum('decision', array_column(MEDIA_POST_REVIEW_DECISION::cases(), 'value'))->nullable();
      $table->text('comment')->nullable();


      $table->timestamps();

      $table->unique(['post_id', 'reviewer_type', 'reviewer_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('media_post_reviews');
  }
};
