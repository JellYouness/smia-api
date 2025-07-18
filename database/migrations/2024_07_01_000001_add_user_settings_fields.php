<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('language')->nullable();
            $table->boolean('notification_email')->default(true);
            $table->boolean('notification_sms')->default(false);
            $table->boolean('notification_push')->default(true);
            $table->boolean('notification_in_app')->default(true);
            $table->string('privacy')->default('PUBLIC');
            $table->string('two_factor_secret')->nullable();
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'language',
                'notification_email',
                'notification_sms',
                'notification_push',
                'notification_in_app',
                'privacy',
                'two_factor_secret',
                'google_id',
                'facebook_id',
            ]);
        });
    }
};
