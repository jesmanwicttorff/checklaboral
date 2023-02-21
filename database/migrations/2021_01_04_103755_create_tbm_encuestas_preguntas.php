<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbmEncuestasPreguntas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbm_encuestas_preguntas",function(Blueprint $table){
          $table->increments('pregunta_id');
          $table->integer('TipoPregunta_id');
          $table->string('pregunta');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbm_encuestas_preguntas');
    }
}
