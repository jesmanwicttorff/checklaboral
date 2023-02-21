<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblKpiTiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_kpis_tipos', function (Blueprint $table) {
            //
            $table->string('Descripcion',128)->nullable(true)->change();
            $table->integer('RangoInferior')->default(1)->change();
            $table->integer('RangoSuperior')->default(1)->change();
            $table->integer('updated_by')->nullable(true)->change();
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
            $table->string('Descripcion',128)->nullable(false)->change();
            $table->integer('RangoInferior')->default(0)->nullable(false)->change();
            $table->integer('RangoSuperior')->default(0)->nullable(false)->change();
            $table->integer('updated_by')->nullable(false)->change();
        });
    }
}
