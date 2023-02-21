<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDiferenciasCalculoPieMaestro extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tbl_diferencias_calculo_pie_maestro', function (Blueprint $table) {
          $table->increments('id');
          $table->string('grupo',128);
          $table->string('nombre',128);
          $table->string('nombre_campo',128);
          $table->integer('id_estatus');
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
        Schema::drop('tbl_diferencias_calculo_pie_maestro');
    }
}
