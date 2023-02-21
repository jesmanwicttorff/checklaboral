<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbmEncuestaCategoriaPonderacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbm_encuesta_categoria_ponderacion",function(Blueprint $table){
          $table->increments('ponderacionCategoria_id');
          $table->integer('ponderacion');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
