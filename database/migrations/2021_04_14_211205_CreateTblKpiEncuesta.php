<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblKpiEncuesta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("tbl_kpi_encuesta",function(Blueprint $table){
            $table->increments('KpiEncuestaId');
            $table->integer('encuesta_id');
            $table->date('createOn');
            $table->integer('kpi_id');
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
