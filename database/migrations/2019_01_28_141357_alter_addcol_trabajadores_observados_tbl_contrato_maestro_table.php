<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTrabajadoresObservadosTblContratoMaestroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato_maestro', function (Blueprint $table) {
          $table->integer('trabajadores_con_o')->after('dotacion');
          $table->integer('trabajadores_con_ol')->after('porcentaje_ol');
          $table->integer('trabajadores_con_op')->after('porcentaje_op');
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
          $table->dropcolumn('trabajadores_con_o');
          $table->dropcolumn('trabajadores_con_ol');
          $table->dropcolumn('trabajadores_con_op');
        });
    }
}
