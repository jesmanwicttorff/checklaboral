<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAccesos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_accesos', function (Blueprint $table) {
            $table->string('data_rut',20)->nullable(true);
            $table->string('data_nombres',50)->nullable(true);
            $table->string('data_apellidos', 50)->nullable(true);
            $table->string('entry_by_access',50)->nullable(true);
            $table->dropForeign('fk_accesos_personas1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_accesos', function (Blueprint $table) {
            $table->dropcolumn('data_rut');
            $table->dropcolumn('data_nombres');
            $table->dropColumn('data_apellidos');
            $table->dropColumn('entry_by_access');
            $table->dropForeign('fk_accesos_personas1');
            $table->foreign('IdPersona')->references('IdPersona')->on('tbl_personas');

        });
    }
}
