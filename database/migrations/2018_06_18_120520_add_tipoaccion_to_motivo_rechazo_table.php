<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTipoaccionToMotivoRechazoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_motivo_rechazo', function (Blueprint $table) {
            $table->integer('TipoMotivo')->after('Descripcion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_motivo_rechazo', function (Blueprint $table) {
            $table->dropcolumn('TipoMotivo');
        });
    }
}
