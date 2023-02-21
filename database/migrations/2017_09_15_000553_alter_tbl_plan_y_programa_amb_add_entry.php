<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPlanYProgramaAmbAddEntry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_planes_y_programas_ambito', function (Blueprint $table) {
            $table->integer("entry_by")->after('IdEstatus');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_planes_y_programas_ambito', function (Blueprint $table) {
            $table->dropColumn('entry_by');
        });
    }
}
