<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTblPersonasAcreditacionContratoIdIdContratista extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_personas_acreditacion', function (Blueprint $table) {
            $table->integer('contrato_id')->after('acreditacion');
            $table->integer('idcontratista')->after('acreditacion');
            $table->date('acreditacion')->nullable()->change();

            $table->foreign('idcontratista')->references('IdContratista')->on('tbl_contratistas');
            $table->foreign('contrato_id')->references('contrato_id')->on('tbl_contrato');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_personas_acreditacion', function (Blueprint $table) {
            $table->dropcolumn('contrato_id');
            $table->dropcolumn('idcontratista');
        });
    }
}
