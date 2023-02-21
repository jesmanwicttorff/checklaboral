<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbmEncuestasCategoriasPreguntas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbm_encuestas_categorias_preguntas",function(Blueprint $table){
          $table->increments('categoriaPregunta_id');
          $table->integer('ponderacionCategoria');
          $table->string('tituloCategoria');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbm_encuestas_categorias_preguntas');
    }
}
