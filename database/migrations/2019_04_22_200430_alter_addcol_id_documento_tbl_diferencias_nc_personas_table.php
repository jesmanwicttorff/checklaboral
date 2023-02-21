<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolIdDocumentoTblDiferenciasNcPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_diferencias_nc_personas', function (Blueprint $table) {
            $table->integer('IdDocumento')->after('periodo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_diferencias_nc_personas', function (Blueprint $table) {
            $table->dropcolumn('IdDocumento');
        });
    }
}
