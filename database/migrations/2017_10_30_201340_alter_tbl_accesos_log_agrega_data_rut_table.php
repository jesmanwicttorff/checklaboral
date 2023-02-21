<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAccesosLogAgregaDataRutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_accesos_log', function (Blueprint $table) {
            $table->string('data_rut',20)->nullable(true)->after('IdEntidad');
            $table->string('data_nombres',50)->nullable(true)->after('data_rut');
            $table->string('data_apellidos',50)->nullable(true)->after('data_nombres');
            $table->integer('contrato_id')->nullable(true)->after('data_apellidos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_accesos_log', function (Blueprint $table) {
            $table->dropcolumn('data_rut');
            $table->dropcolumn('data_nombres');
            $table->dropcolumn('data_apellidos');
        });
    }
}
