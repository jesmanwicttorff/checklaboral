<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddcolIdContratistaTblF301EmpleadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_f30_1_empleados', function (Blueprint $table) {
            $table->integer("IdContratista")->nullable(true)->after('contrato_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_f30_1_empleados', function (Blueprint $table) {
            $table->dropcolumn("IdContratista");
        });
    }
}
