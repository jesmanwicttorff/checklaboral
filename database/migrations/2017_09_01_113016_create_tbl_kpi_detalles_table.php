<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblKpiDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_kpis_detalles', function (Blueprint $table) {
            $table->increments('IdKpiDetalle');
            $table->integer('IdKpi')->unsigned();
            $table->date('Fecha');
            $table->integer('Puntaje')->nullable(true);
            $table->integer('Resultado')->nullable(true);
            $table->integer('MetaSuperior')->nullable(true);
            $table->integer('MetaInferior')->nullable(true);
            $table->integer('entry_by');
            $table->integer('updated_by')->nullable(true);
            $table->timestamps();
            $table->foreign('IdKpi')->references('IdKpi')->on('tbl_kpis');
            $table->foreign('entry_by')->references('id')->on('tb_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_kpis_detalles');
    }
}
