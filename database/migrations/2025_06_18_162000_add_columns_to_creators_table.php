<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCreatorsTable extends Migration
{
    public function up(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            // Ne rien ajouter, toutes les colonnes attendues sont déjà présentes
        });
    }

    public function down(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            // Ne rien supprimer
        });
    }
}
