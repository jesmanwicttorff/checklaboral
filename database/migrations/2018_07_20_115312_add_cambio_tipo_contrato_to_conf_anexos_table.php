<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCambioTipoContratoToConfAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_conf_anexos', function (Blueprint $table) {
            $table->integer('CambioTipoContrato')->after('CambioOtros')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_conf_anexos', function (Blueprint $table) {
            $table->dropcolumn('CambioTipoContrato');
        });
    }
}
