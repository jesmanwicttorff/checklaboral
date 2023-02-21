<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbmRelacionEncuestaCategoria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbm_relacion_encuesta_categoria",function(Blueprint $table){
          $table->integer('encuesta_id');
          $table->integer('categoriaPregunta_id');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbm_relacion_encuesta_categoria');
    }
}
