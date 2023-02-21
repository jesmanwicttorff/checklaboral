<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblTrabajadorEliminadoRegistro extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('tbl_trabajador_eliminado_registro', function (Blueprint $table) {
           
            $table->increments('id');
            $table->integer('IdPersona');
            $table->string('RUT');
            $table->string('numero_contrato');
            $table->integer('contrato_id');
            $table->integer('doc_borrados')->comment("cantidad de documentos borrados");
            $table->integer('doc_hist_borrados')->comment("cantidad de documentos historicos borrados");
            $table->integer('persAcre_borrados')->comment("cantidad de registros en personas acreditadas borrados");
            $table->datetime('eliminated_at');


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
        Schema::drop('tbl_trabajador_eliminado_registro');
    }
}
