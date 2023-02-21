<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolOtrosTblContratosPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contratos_personas', function (Blueprint $table) {
            $table->string('Otros',128);
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
            $table->dropcolumn('Otros');
        });
    }
}
