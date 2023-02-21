<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblReportesExternosRedimenUpObjetoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_reportes_externos_detalle', function (Blueprint $table) {
            $table->string('objeto',190)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_reportes_externos_detalle', function (Blueprint $table) {
            $table->string('objeto',128)->change();
        });
    }
}
