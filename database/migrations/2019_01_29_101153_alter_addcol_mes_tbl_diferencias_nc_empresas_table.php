<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolMesTblDiferenciasNcEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_diferencias_nc_empresas', function (Blueprint $table) {
            $table->date('mes')->after('periodo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_diferencias_nc_empresas', function (Blueprint $table) {
            $table->dropcolumn('mes');
        });
    }
}
