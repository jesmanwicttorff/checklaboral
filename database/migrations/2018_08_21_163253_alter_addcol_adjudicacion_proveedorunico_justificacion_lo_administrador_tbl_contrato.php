<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolAdjudicacionProveedorunicoJustificacionLoAdministradorTblContrato extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato', function (Blueprint $table) {
            $table->integer('IdTipoAdjudicacion')->after('cont_estado');
            $table->integer('ProveedorUnico')->after('IdTipoAdjudicacion');
            $table->integer('JustificacionProveedorUnico')->after('ProveedorUnico');
            $table->integer('LibroObra')->after('JustificacionProveedorUnico');
            $table->integer('IdAdministradorContratista')->after('LibroObra');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contrato', function (Blueprint $table) {
            $table->dropcolumn('IdTipoAdjudicacion');
            $table->dropcolumn('ProveedorUnico');
            $table->dropcolumn('JustificacionProveedorUnico');
            $table->dropcolumn('LibroObra');
            $table->dropcolumn('IdAdministradorContratista');
        });
    }
}
