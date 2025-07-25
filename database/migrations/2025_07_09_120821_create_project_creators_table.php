<?php

use App\Enums\CREATOR_PROJECT_PERMISSION;
use App\Enums\CREATOR_PROJECT_STATUS;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('project_creators', function (Blueprint $table) {
      $table->id();
      $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
      $table->foreignId('creator_id')->constrained('creators')->onDelete('cascade');
      $table->string('role')->nullable();
      $table->enum('status', array_column(CREATOR_PROJECT_STATUS::cases(), 'value'))->default(CREATOR_PROJECT_STATUS::CONFIRMED->value);
      $table->enum('permission', array_column(CREATOR_PROJECT_PERMISSION::cases(), 'value'))->nullable();
      $table->timestamps();

      $table->unique(['project_id', 'creator_id']);
      $table->index('role');
      $table->index('status');
      $table->index('permission');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('project_creators');
  }
};
