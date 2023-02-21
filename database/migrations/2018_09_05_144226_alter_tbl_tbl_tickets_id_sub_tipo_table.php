<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblTblTicketsIdSubTipoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_tickets', function (Blueprint $table) {
            $table->integer('IdSubTipo')->after('IdTipo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_tickets', function (Blueprint $table) {
            $table->dropcolumn('IdSubTipo');
        });
    }
}
