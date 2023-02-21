<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblPeriodoControlado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_periodo_controlado', function (Blueprint $table) {
            $table->increments('id_pcontrolado');
            $table->string('apertura');
            $table->string('cierre');
            $table->string('informe_final');
            $table->string('periodo_controlado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_periodo_controlado');
    }
}
