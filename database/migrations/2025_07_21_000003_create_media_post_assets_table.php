<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('media_post_assets', function (Blueprint $table) {
      $table->id();
      $table->foreignId('post_id')->constrained('media_posts')->onDelete('cascade');
      $table->integer('version');
      $table->foreignId('upload_id')->nullable()->constrained('uploads')->cascadeOnDelete();
      $table->string('mime_type', 100)->nullable();
      $table->foreignId('uploaded_by')->nullable()->constrained('creators');

      $table->timestamps();

      $table->index('post_id');
      $table->index('upload_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('media_post_assets');
  }
};
