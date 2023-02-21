<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTituloDescripcionToEncuestasMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_encuestas_master', function (Blueprint $table) {
            $table->text('TextoExplicativo')->after('IdTipoDocumento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_encuestas_master', function (Blueprint $table) {
            $table->dropcolumn('TextoExplicativo');
        });
    }
}
