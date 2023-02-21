<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDiferenciasCalculoPie extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_diferencias_calculo_pie', function (Blueprint $table) {
            $table->increments('id');
            $table->date('periodo');
            $table->integer('contrato_id');
            $table->string('nombre_campo');
            $table->string('nombre');
            $table->double('indicador',8,3);
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
        Schema::drop('tbl_diferencias_calculo_pie');
    }
}
