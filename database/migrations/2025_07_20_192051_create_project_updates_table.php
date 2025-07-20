<?php

use App\Enums\PROJECT_UPDATE_TYPE;
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
    Schema::create('project_updates', function (Blueprint $table) {
      $table->id();
      $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
      $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
      $table->foreignId('ambassador_id')->nullable()->constrained('ambassadors')->onDelete('set null');
      $table->text('body');
      $table->enum('type', array_column(PROJECT_UPDATE_TYPE::cases(), 'value'))->default(PROJECT_UPDATE_TYPE::UPDATE->value);
      $table->timestamps();

      $table->index(['project_id']);
      $table->index(['client_id']);
      $table->index(['ambassador_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('project_updates');
  }
};
