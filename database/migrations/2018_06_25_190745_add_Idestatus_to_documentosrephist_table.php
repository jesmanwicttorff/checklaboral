<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdestatusToDocumentosrephistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->date('FechaEmision')->after('DetalleEntidad')->nullable(true);
            $table->dropcolumn('DetalleEntidad');
            $table->integer('IdEstatus')->after('FechaVencimiento');
            $table->string('Resultado',256)->after('IdEstatusDocumento')->nullable(true);
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
            $table->string('DetalleEntidad',11)->after('DocumentoURL');
            $table->dropcolumn('FechaEmision');
            $table->dropcolumn('IdEstatus');
            $table->dropcolumn('Resultado');
        });
    }
}
