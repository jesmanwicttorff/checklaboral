<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSolicitarToTblTipoDocumentoValor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_tipo_documento_valor', function (Blueprint $table) {
            $table->boolean('Solicitar')->after('Requerido')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_tipo_documento_valor', function (Blueprint $table) {
            $table->dropcolumn('Solicitar');
        });
    }
}
