<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolIdEstatusTblRiegoTabla extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_riesgo', function (Blueprint $table) {
            $table->integer('IdEstatus');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_riesgo', function (Blueprint $table) {
            $table->dropcolumn('IdEstatus');
        });
    }
}
