<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTblDocumentoRepHistoricoTimestampTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->timestamps();
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
            $table->dropcolumn('created_at');
            $table->dropcolumn('updated_at');
        });
    }
}
