<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblEncuestaCuatrimestre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("tbl_encuesta_cuatrimestre",function(Blueprint $table){
            $table->increments('EncuestaCuatrimestreId');
            $table->integer('contrato_id');
            $table->integer('kpi_id');
            $table->string('calificacion');
            $table->string('notafinal');
            $table->integer('categoria_id');
            $table->string('periodo');
            $table->integer('IdTipoDocumento');
            $table->integer('encuesta_id');
            $table->date('fechaCreate');
            $table->date('fechaUpdate');
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
