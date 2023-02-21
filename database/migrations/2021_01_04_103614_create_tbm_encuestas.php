<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbmEncuestas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbm_encuestas",function(Blueprint $table){
          $table->increments('encuesta_id');
          $table->integer('contrato_id');
          $table->integer('IdContratista');
          $table->integer('escalaPuntuacion_id');
          $table->string('titulo');
          $table->string('resumen');
          //$table->decimal('notaGlobal',5,2);
          //$table->string('pdf');
          $table->integer('IdTipoDocumento');
          $table->integer('entry_by');
          $table->integer('entry_by_access');
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
        Schema::drop('tbm_encuestas');
    }
}
