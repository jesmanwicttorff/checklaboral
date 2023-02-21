<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAccesosLogAgregaIdTipoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_accesos_log', function (Blueprint $table) {
            $table->integer('IdTipoAcceso')->nullable(true)->after('IdAccesoLog');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_accesos_log', function (Blueprint $table) {
            $table->dropcolumn('IdTipoAcceso');
        });
    }
}
