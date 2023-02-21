<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolEntryByTblDocumentosRepHistorico extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
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
        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->dropcolumn('entry_by');
        });
    }
}
