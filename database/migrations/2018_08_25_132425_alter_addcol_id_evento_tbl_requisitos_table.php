<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolIdEventoTblRequisitosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_requisitos', function (Blueprint $table) {
            $table->integer('IdEvento')->after('IdTipoDocumento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_requisitos', function (Blueprint $table) {
            $table->dropcolumn('IdEvento');        
        });
    }
}
