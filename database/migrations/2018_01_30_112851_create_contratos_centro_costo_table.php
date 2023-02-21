<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContratosCentroCostoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contratos_centrocosto', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contrato_id');
            $table->integer('centrocosto_id');
            $table->timestamps();

        });
        Schema::table('tbl_contratos_centrocosto', function($table) {
            $table->foreign('contrato_id')->references('contrato_id')->on('tbl_contrato');
            $table->foreign('centrocosto_id')->references('claseCosto_id')->on('tbl_contclasecosto');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_contratos_centrocosto');
    }
}
