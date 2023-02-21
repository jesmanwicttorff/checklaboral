<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolNcGeneradasAnteriorTblContratoMaestroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato_maestro', function (Blueprint $table) {
            $table->integer('nc_generadas')->after('documentacion');
            $table->integer('nc_generadas_anterior')->after('nc_generadas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contrato_maestro', function (Blueprint $table) {
            $table->dropcolumn('nc_generadas');
            $table->dropcolumn('nc_generadas_anterior');
        });
    }
}
