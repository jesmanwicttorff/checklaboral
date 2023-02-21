<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblKpisTiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_kpis_tipos', function (Blueprint $table) {
            $table->string("Nombre",48)->after('IdTipo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_kpis_tipos', function (Blueprint $table) {
            $table->dropColumn('Nombre');
        });
    }
    
}
