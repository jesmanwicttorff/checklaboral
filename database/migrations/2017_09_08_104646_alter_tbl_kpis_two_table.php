<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblKpisTwoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_kpis', function (Blueprint $table) {
            $table->string('Descripcion',128)->nullable(true)->change();
            $table->string('Formula',128)->nullable(true)->change();
            $table->decimal('RangoSuperior',11,2)->nullable(true)->change();
            $table->decimal('RangoInferior',11,2)->nullable(true)->change();
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
        Schema::table('tbl_kpis', function (Blueprint $table) {
            $table->string('Descripcion',128)->nullable(false)->change();
            $table->string('Formula',128)->nullable(true)->change();
            $table->integer('RangoSuperior')->nullable(true)->change();
            $table->integer('RangoInferior')->nullable(true)->change();
            $table->integer('updated_by')->change();
        });
    }
}
