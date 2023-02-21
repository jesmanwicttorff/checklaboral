<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentosrepHistorico extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->dateTime('FechaAprobacion')->nullable()->change();
            $table->integer('load_by')->nullable()->change();
            $table->integer('approv_by')->nullable()->change();
        });

        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->dropColumn('Documento');
        });

        Schema::table('tbl_documentos_rep_historico', function (Blueprint $table) {
            $table->binary('Documento')->nullable()->after('IdEntidad');
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
            $table->dateTime('FechaAprobacion')->nullable(false)->change();
            $table->integer('load_by')->nullable(false)->change();
            $table->integer('approv_by')->nullable(false)->change();
        });
    }
}
