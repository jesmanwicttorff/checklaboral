<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblremuneracionespptoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create("tbl_remuneraciones_ppto",function(Blueprint $table){
            $table->increments('id_remuneracion_ppto');
            $table->integer('IdRol');
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
        Schema::drop("tbl_remuneraciones_ppto");
    }
}
