<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoEdp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_edp",function(Blueprint $table){
          $table->increments('edp_id');
          $table->integer('contrato_id');
          $table->string('nombre_edp');
          $table->date('fechaIngreso');
          $table->date('fechaEnvio');
          $table->integer('estado_id');
          $table->integer('numero_edp');
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
