<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolTblEntidadesParaRequisitos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_entidades', function (Blueprint $table) {
            $table->integer('para_requisito');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_entidades', function (Blueprint $table) {
            $table->dropcolumn('para_requisito');
        });
    }
}
