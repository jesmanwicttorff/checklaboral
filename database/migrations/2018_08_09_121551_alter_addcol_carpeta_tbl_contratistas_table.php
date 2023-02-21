<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolCarpetaTblContratistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contratistas', function (Blueprint $table) {
            $table->string('NombreCarpeta',128);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contratistas', function (Blueprint $table) {
            $table->dropcolumn('NombreCarpeta');
        });
    }
}
