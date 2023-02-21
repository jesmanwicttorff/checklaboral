<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratosControlLaboralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contrato_control_laboral', function (Blueprint $table) {
            $table->increments('id');
            $table->date('periodo');
            $table->integer('contrato_id');
            $table->string('cont_numero',30);
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
        Schema::drop('tbl_contrato_control_laboral');
    }
}
