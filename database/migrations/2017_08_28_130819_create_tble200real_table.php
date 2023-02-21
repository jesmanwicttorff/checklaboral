<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTble200realTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create("tbl_e200_real",function(Blueprint $table){
            $table->increments('id_e200_real');
            $table->integer('hh_h');
            $table->integer('hh_m');
            $table->integer('cantidad_h');
            $table->integer('cantidad_m');
            $table->integer('contrato_id');
            $table->date('mes');
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
        Schema::drop("tbl_e200_real");
    }
}
