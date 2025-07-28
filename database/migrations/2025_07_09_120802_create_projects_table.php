<?php

use App\Enums\PROJECT_STATUS;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('status', array_column(PROJECT_STATUS::cases(), 'value'))->default(PROJECT_STATUS::DRAFT->value);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->decimal('budget', 12, 2);
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('creator_id')->nullable()->constrained('creators')->onDelete('set null');
            $table->foreignId('ambassador_id')->nullable()->constrained('ambassadors')->onDelete('set null');
            $table->timestamps();
            $table->index('status');
            $table->index('client_id');
            $table->index('creator_id');
            $table->index('ambassador_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
