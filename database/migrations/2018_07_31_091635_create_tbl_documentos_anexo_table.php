<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDocumentosAnexoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_documentos_anexos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('IdDocumento');
            $table->integer('contrato_id');
            $table->integer('IdTipoContrato');
            $table->date('FechaVencimiento');
            $table->integer('IdRol');
            $table->integer('IdEstatus');
            $table->integer('entry_by');
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
        Schema::drop('tbl_documentos_anexos');
    }
}
