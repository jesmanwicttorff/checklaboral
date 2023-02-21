<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdTipoContratoAndFechaVencimientoToTblContratosPersonas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contratos_personas', function (Blueprint $table) {
            $table->integer('IdTipoContrato')->after('IdEstatus')->nullable(true);
            $table->date('FechaVencimiento')->after('IdTipoContrato')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contratos_personas', function (Blueprint $table) {
            $table->dropcolumn('IdTipoContrato');
            $table->dropcolumn('FechaVencimiento');
        });
    }
}
