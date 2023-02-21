<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiasvencimientoToTiposDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_tipos_documentos', function (Blueprint $table) {
            $table->integer('DiasVencimiento')->nullable()->after('IdProceso');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_tipos_documentos', function (Blueprint $table) {
            $table->dropcolumn('DiasVencimiento');
        });
    }
}
