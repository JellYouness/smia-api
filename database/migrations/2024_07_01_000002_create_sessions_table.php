<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token_hash', 64)->nullable(); // Hash of the Sanctum token
            $table->string('device')->nullable();
            $table->string('ip_address', 45)->nullable(); // IPv6 compatible
            $table->text('user_agent')->nullable();
            $table->timestamp('last_active')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'token_hash']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};
