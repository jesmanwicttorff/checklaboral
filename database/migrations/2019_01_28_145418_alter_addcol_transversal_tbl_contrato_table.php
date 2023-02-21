<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTransversalTblContratoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato', function (Blueprint $table) {
            $table->integer('transversal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contrato', function (Blueprint $table) {
            $table->dropcolumn('transversal');
        });
    }
}
