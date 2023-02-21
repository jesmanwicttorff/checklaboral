<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTble200mensualTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create("tbl_e200_mensual",function(Blueprint $table){
            $table->increments('id_e200_mensual');
            $table->integer('IdDocumento');
            $table->date('mes');
            $table->integer('cantidad')->default(0);
            $table->integer('faena')->default(0);
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
        Schema::drop("tbl_e200_mensual");
    }
}
