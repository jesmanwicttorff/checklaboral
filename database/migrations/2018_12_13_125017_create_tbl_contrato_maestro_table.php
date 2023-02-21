<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoMaestroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contrato_maestro', function (Blueprint $table) {
            $table->increments('id');
            $table->date('periodo');
            $table->integer('idcontratista');
            $table->integer('contrato_id');
            $table->integer('dotacion');
            $table->decimal('costo_laboral',10,2)->default(0.0);
            $table->decimal('pasivo_laboral',10,2)->default(0.0);
            $table->integer('obligaciones_laborales')->default(0);
            $table->integer('obligaciones_previsionales')->default(0);
            $table->integer('finiquitos')->default(0);
            $table->integer('documentacion')->default(0);
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
        Schema::drop('tbl_contrato_maestro');
    }
}
