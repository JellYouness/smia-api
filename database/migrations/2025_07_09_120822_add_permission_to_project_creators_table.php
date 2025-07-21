<?php

use App\Enums\CREATOR_PROJECT_PERMISSION;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('project_creators', function (Blueprint $table) {
      $table->enum('permission', array_column(CREATOR_PROJECT_PERMISSION::cases(), 'value'))->default(CREATOR_PROJECT_PERMISSION::VIEWER->value)->after('status');
      $table->index('permission');
    });
  }

  public function down(): void
  {
    Schema::table('project_creators', function (Blueprint $table) {
      $table->dropIndex(['permission']);
      $table->dropColumn('permission');
    });
  }
};
