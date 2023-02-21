<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoItemizadoEtapas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_itemizado_etapas",function(Blueprint $table){
          $table->increments('etapa_id');
          $table->string('etapa');
          $table->integer('perfil_id');
          $table->string('propiedades');
          $table->integer('contrato_id');
          $table->integer('itemizado_id');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_contrato_itemizado_etapas');
    }
}
