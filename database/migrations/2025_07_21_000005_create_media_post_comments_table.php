<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('media_post_comments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('post_id')->constrained('media_posts')->onDelete('cascade');
      $table->foreignId('asset_id')->nullable()->constrained('media_post_assets');
      $table->foreignId('author_id')->constrained('users');
      $table->text('body');
      $table->integer('timecode')->nullable();

      $table->timestamps();

      $table->index('post_id');
      $table->index('asset_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('media_post_comments');
  }
};
