<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbmRelacionEncuestaCategoriaPregunta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbm_relacion_encuesta_categoria_pregunta",function(Blueprint $table){
          $table->integer('categoriaPregunta_id');
          $table->integer('pregunta_id');
          $table->integer('encuesta_id');
          $table->integer('ponderacion');
          $table->timestamps();
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
