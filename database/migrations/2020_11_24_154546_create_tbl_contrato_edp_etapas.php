<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoEdpEtapas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_edp_etapas",function(Blueprint $table){
          $table->increments('etapa_id');
          $table->string('etapa');
          $table->string('perfil_id');
          $table->string('propiedades');
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
