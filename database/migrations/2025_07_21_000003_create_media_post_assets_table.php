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
      $table->foreignId('version_id')->nullable()->constrained('media_post_versions')->cascadeOnDelete();
      $table->boolean('is_reference')->default(false);

      $table->foreignId('upload_id')->nullable()->constrained('uploads')->cascadeOnDelete();
      $table->string('mime_type', 100)->nullable();
      $table->foreignId('uploaded_by')->nullable()->constrained('users');

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
