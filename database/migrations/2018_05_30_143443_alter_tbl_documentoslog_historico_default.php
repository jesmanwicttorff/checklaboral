<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentoslogHistoricoDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos_log_historico', function (Blueprint $table) {
            $table->dateTime('createdOn')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_documentos_log_historico', function (Blueprint $table) {
            $table->dateTime('createdOn')->nullable(false)->change();
        });
    }
}
