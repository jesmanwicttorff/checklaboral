<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolFechaInicioFaenaTblContratosPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contratos_personas', function (Blueprint $table) {
            $table->date('FechaInicioFaena')->after('IdRol')->nullable();
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
            $table->dropcolumn('FechaInicioFaena');
        });
    }
}
