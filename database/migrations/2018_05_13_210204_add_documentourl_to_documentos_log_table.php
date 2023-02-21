<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentourlToDocumentosLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos_log', function (Blueprint $table) {
            $table->string('DocumentoURL',128)->nullable()->after('IdAccion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_documentos_log', function (Blueprint $table) {
            $table->dropcolumn('DocumentoURL');
        });
    }
}
