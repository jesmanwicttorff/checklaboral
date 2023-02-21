<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolPorcentajeTblContratoMaestroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato_maestro', function (Blueprint $table) {
            $table->decimal('porcentaje_ol',8,3)->after('obligaciones_laborales');
            $table->decimal('porcentaje_op',8,3)->after('obligaciones_previsionales');
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
            //
            $table->dropcolumn('porcentaje_ol');
            $table->dropcolumn('porcentaje_op');
        });
    }
}
