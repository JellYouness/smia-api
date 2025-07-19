<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['direct', 'group', 'project'])->default('direct');
            $table->string('name')->nullable(); // For group chats
            $table->unsignedBigInteger('project_id')->nullable(); // For project-related chats
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->index(['type', 'project_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
};
