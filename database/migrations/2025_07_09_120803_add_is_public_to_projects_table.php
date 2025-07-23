<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            //if not exists, add is_public column
            if (!Schema::hasColumn('projects', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('id');
            }
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            //if exists, drop is_public column
            if (Schema::hasColumn('projects', 'is_public')) {
                $table->dropColumn('is_public');
            }
        });
    }
};
