<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNoConformidadesPersonas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_no_conformidades_personas', function (Blueprint $table) {
            $table->increments('id');
            $table->date('periodo');
            $table->integer('contrato_id');
            $table->string('cont_numero',128);
            $table->integer('IdPersona');
            $table->string('rut',128);
            $table->string('nombre',128);
            $table->integer('IdTipoDocumento');
            $table->string('Documento',128);
            $table->integer('IdEstatus');
            $table->integer('Estatus');
            $table->string('Comentario',128);
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
        Schema::drop('tbl_no_conformidades_personas');
    }
}
