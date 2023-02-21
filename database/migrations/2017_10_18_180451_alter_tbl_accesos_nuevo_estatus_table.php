<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAccesosNuevoEstatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_accesos', function (Blueprint $table) {
            $table->string('IdEstatusUsuario',20)->nullable(true)->after('IdEstatus');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_accesos', function (Blueprint $table) {
            $table->dropcolumn('IdEstatusUsuario');
        });
    }
}
