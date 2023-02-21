<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolPagadosNoPagadosTblF301Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_f30_1', function (Blueprint $table) {
            $table->integer("TrabajadoresNoPagados")->after("TrabajadoresDesvinculados");
            $table->integer("TrabajadoresPagados")->after("TrabajadoresDesvinculados");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_f30_1', function (Blueprint $table) {
            $table->dropcolumn("TrabajadoresPagados");
            $table->dropcolumn("TrabajadoresNoPagados");
        });
    }
}
