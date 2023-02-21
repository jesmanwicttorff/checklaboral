<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAlertaDetalleAddEntryBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_alerta_detalle', function (Blueprint $table) {
            //
			$table->integer('entry_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_alerta_detalle', function (Blueprint $table) {
            //
			$table->dropcolumn('entry_by');
        });
    }
}
