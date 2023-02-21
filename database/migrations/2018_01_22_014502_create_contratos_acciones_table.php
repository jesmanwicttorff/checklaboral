<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContratosAccionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contratos_acciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contrato_id');
            $table->integer('accion_id');
            $table->text('observaciones')->nullable();
            $table->integer('entry_by');
            $table->timestamp('createdOn')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('contrato_id')->references('contrato_id')->on('tbl_contrato');
            $table->foreign('accion_id')->references('IdAccion')->on('tbl_acciones');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_contratos_acciones');
    }
}
