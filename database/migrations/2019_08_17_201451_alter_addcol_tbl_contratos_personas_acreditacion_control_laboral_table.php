<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTblContratosPersonasAcreditacionControlLaboralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contratos_personas', function (Blueprint $table) {
            $table->integer('acreditacion')->default('1');
            $table->integer('controllaboral')->default('1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contratos_personas', function (Blueprint $table) {
            $table->dropcolumn('acreditacion');
            $table->dropcolumn('controllaboral');
        });
    }
}
