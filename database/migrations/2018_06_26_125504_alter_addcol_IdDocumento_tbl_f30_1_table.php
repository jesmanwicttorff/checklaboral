<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolIdDocumentoTblF301Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_f30_1', function (Blueprint $table) {
            $table->integer("IdDocumento")->after('IdF301');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_f30_1', function (Blueprint $table) {
            $table->dropcolumn("IdDocumento");
        });
    }
}
