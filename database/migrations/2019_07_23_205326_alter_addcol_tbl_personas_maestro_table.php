<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTblPersonasMaestroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_personas_maestro', function (Blueprint $table) {
            $table->date('FechaAnterior')->nullabla();
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
            $table->dropcolumn('FechaAnterior');
        });
    }
}
