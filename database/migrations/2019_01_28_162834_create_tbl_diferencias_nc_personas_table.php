<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDiferenciasNcPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_diferencias_nc_personas', function (Blueprint $table) {
            $table->increments('id');
            $table->date('periodo');
            $table->integer('contrato_id');
            $table->string('cont_numero',20);
            $table->integer('IdPersona');
            $table->string('rut',20);
            $table->string('nombre',128);
            $table->integer('IdTipoDocumento');
            $table->string('TipoDocumento',128);
            $table->integer('IdEstatusDocumento');
            $table->string('EstatusDocumento',128);
            $table->integer('IdEstatus');
            $table->string('Resultado',256);
            $table->integer('entry_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->index(['periodo']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_diferencias_nc_personas');
    }
}
