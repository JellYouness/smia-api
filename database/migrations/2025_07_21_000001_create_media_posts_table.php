<?php

use App\Enums\MEDIA_POST_STATUS;
use App\Enums\MEDIA_POST_PRIORITY;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('media_posts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
      $table->string('title', 255)->nullable();
      $table->text('description')->nullable();
      $table->enum('status', array_column(MEDIA_POST_STATUS::cases(), 'value'))->default(MEDIA_POST_STATUS::BACKLOG->value);
      $table->enum('priority', array_column(MEDIA_POST_PRIORITY::cases(), 'value'))->default(MEDIA_POST_PRIORITY::MED->value);
      $table->integer('position')->nullable();
      $table->date('due_date')->nullable();
      $table->timestamps();
      $table->index('project_id');
      $table->index('status');
      $table->index('priority');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('media_posts');
  }
};
