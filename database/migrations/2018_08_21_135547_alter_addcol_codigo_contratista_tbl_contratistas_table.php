<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolCodigoContratistaTblContratistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contratistas', function (Blueprint $table) {
            $table->string('CodigoProveedor',128)->after('Rut');
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
            $table->dropcolumn('CodigoProveedor');
        });
    }
}
