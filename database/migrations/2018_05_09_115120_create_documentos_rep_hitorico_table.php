<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentosRepHitoricoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->increments('IdDocumentoH');
            $table->integer('IdTipoDocumento');
            $table->integer('Entidad');
            $table->integer('IdEntidad');
            $table->binary('Documento');
            $table->dateTime('FechaAprobacion');
            $table->dateTime('FechaVencimiento')->nullable();
            $table->integer('load_by');
            $table->integer('approv_by');
            $table->integer('contrato_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_documentos_rep_historico');
    }
}
