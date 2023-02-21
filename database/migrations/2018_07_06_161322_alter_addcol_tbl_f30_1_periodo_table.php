<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTblF301PeriodoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_f30_1', function (Blueprint $table) {
            //
            $table->date("Periodo")->nullable(true)->after('IdDocumento');
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
            //
            $table->dropcolumn("Periodo");
        });
    }
}
