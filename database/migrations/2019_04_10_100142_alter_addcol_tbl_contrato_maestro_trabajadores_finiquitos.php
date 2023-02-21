<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTblContratoMaestroTrabajadoresFiniquitos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato_maestro', function (Blueprint $table) {
            $table->decimal('porcentaje_f',8,3)->after('finiquitos');
            $table->integer('trabajadores_con_f')->after('porcentaje_f');
            $table->integer('trabajadores_con_of')->after('trabajadores_con_f');
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
            $table->dropcolumn('trabajadores_con_of');
            $table->dropcolumn('trabajadores_con_f');
            $table->dropcolumn('porcentaje_f');
        });
    }
}
