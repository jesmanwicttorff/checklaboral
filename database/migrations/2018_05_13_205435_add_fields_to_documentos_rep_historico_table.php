<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToDocumentosRepHistoricoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->string('DetalleEntidad',11)->nullable()->after('Documento');
            $table->integer('IdEstatusDocumento')->nullable()->after('FechaVencimiento');
            $table->integer('IdContratista')->nullable()->after('contrato_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->dropcolumn('DetalleEntidad');
            $table->dropcolumn('IdEstatusDocumento');
            $table->dropcolumn('IdContratista');
        });
    }
}
