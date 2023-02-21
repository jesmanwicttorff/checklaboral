<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolFechaEfectivaTblMovimientoPersonalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_movimiento_personal', function (Blueprint $table) {
            $table->date('FechaEfectiva');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_movimiento_personal', function (Blueprint $table) {
            $table->dropcolumn('FechaEfectiva');
        });
    }
}
