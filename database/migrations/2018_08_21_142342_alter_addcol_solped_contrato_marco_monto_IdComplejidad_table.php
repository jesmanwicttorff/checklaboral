<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolSolpedContratoMarcoMontoIdComplejidadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato', function (Blueprint $table) {
            $table->integer('IdComplejidad')->after('cont_estado');
            $table->date('FechaAdjudicacion')->after('IdComplejidad');
            $table->integer('IdMoneda')->after('FechaAdjudicacion');
            $table->string('Solped',128);
            $table->string('ContratoMarco',128);
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
            $table->dropcolumn('Solped');
            $table->dropcolumn('ContratoMarco');
            $table->dropcolumn('FechaAdjudicacion');
            $table->dropcolumn('IdMoneda');
            $table->dropcolumn('IdComplejidad');
        });
    }
}
