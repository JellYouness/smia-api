<?php

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
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('token_hash', 64)->nullable()->after('user_id'); // Hash of the Sanctum token
            $table->string('ip_address', 45)->nullable()->after('device'); // IPv6 compatible
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->index(['user_id', 'token_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'token_hash']);
            $table->dropColumn(['token_hash', 'ip_address', 'user_agent']);
        });
    }
};
