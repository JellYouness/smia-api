<?php

use App\Enums\ROLE;
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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', array_column(ROLE::cases(), 'value'))->default(ROLE::CLIENT->value)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['CLIENT', 'CREATOR', 'AMBASSADOR', 'ADMIN'])->default('CLIENT')->change();
        });
    }
};
