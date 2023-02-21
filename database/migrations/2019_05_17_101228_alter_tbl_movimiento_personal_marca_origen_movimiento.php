<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMovimientoPersonalMarcaOrigenMovimiento extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_movimiento_personal', function (Blueprint $table) {
            $table->integer('Motivo');
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
            $table->dropcolumn('Motivo');
        });
    }

}
