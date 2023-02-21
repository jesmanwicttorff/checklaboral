<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTble200budgetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create("tbl_e200_budget",function(Blueprint $table){
            $table->increments('id_e200_budget');
            $table->integer('hh_estimada');
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
        Schema::drop("tbl_e200_budget");
    }
}
