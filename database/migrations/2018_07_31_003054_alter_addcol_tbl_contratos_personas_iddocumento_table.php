<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTblContratosPersonasIddocumentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contratos_personas', function (Blueprint $table) {
            $table->integer('IdDocumento')->after('IdRol');
            $table->timestamp('updatedOn');
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
            $table->dropcolumn('IdDocumento');
            $table->dropcolumn('updatedOn');
        });
    }
}
