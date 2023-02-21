<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblKpiTiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_kpis_tipos', function (Blueprint $table) {
            $table->increments('IdTipo');
            $table->string('Descripcion',128);
            $table->integer('RangoInferior')->default(0)->nullable(false);
            $table->integer('RangoSuperior')->default(0)->nullable(false);
            $table->integer('IdEstatus')->default(1)->nullable(false);
            $table->integer('entry_by')->nullable(false);
            $table->integer('updated_by')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_kpis_tipos');
    }
}
