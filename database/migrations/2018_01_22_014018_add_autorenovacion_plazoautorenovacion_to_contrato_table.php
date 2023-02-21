<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutorenovacionPlazoautorenovacionToContratoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato', function (Blueprint $table) {
            $table->integer('autorenovacion')->default(0);
            $table->integer('plazoautorenovacion')->default(0);
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
            $table->dropcolumn('autorenovacion');
            $table->dropcolumn('plazoautorenovacion');
        });
    }
}
