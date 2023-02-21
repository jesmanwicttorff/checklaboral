<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolIdEstatusContratoTblF301EmpleadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_f30_1_empleados', function (Blueprint $table) {
            $table->integer("contrato_id")->nullable(true);
            $table->integer("IdEstatus");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_f30_1_empleados', function (Blueprint $table) {
            $table->dropcolumn("contrato_id");
            $table->dropcolumn("IdEstatus");
        });
    }
}
