<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblremuneracionesMensuales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create("tbl_remuneraciones_mensual",function(Blueprint $table){
            $table->increments('id_remuneraciones_real');
            $table->date('periodo');
            $table->string('cargo',45);
            $table->integer('cantidad');
            $table->integer('prom_remuneracion');
            $table->integer('contrato_id');
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
        Schema::drop("tbl_remuneraciones_mensual");
    }
}
