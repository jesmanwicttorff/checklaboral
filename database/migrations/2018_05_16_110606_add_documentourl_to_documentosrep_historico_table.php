<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentourlToDocumentosrepHistoricoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->string('DocumentoURL',128)->nullable()->after('Documento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->dropcolumn('DocumentoURL');
        });
    }
}
