<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportesGeneradosDetalleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_reportes_generados_detalle', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reporte_id');
            $table->integer('idcontratista');
            $table->integer('contrato_id');
            $table->date('periodo');
            $table->string('url');
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
        Schema::drop('tbl_reportes_generados_detalle');
    }
}
