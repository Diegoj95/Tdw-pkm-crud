<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarNumeroPokedexToPokemon extends Migration
{
    public function up()
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->unsignedInteger('numero_pokedex')->nullable();
        });
    }

    public function down()
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->dropColumn('numero_pokedex');
        });
    }
}
