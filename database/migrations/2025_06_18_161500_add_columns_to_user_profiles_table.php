<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUserProfilesTable extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Ne rien ajouter, toutes les colonnes attendues sont déjà présentes
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Ne rien supprimer
        });
    }
}
