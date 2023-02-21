<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolIdEstatusFechaEfectivaTblPersonasMaestroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_personas_maestro', function (Blueprint $table) {
            //
            $table->string('Estatus',128);
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
        Schema::table('tbl_personas_maestro', function (Blueprint $table) {
            //
            $table->dropcolumn('IdEstatus');
            $table->dropcolumn('FechaEfectiva');
        });
    }
}
