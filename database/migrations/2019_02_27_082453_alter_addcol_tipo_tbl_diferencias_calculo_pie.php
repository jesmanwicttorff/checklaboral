<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTipoTblDiferenciasCalculoPie extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_diferencias_calculo_pie', function (Blueprint $table) {
            $table->string('grupo',120)->after('contrato_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_diferencias_calculo_pie', function (Blueprint $table) {
            $table->dropcolumn('grupo');
        });
    }
}
