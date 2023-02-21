<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoEstatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contrato_estatus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre',128);
            $table->integer('IdEstatus');
            $table->integer('BloqueaAcceso');
            $table->integer('BloqueaCambios');
            $table->integer('BloqueaVinculacion');
            $table->integer('BloqueaDesvinculacion');
            $table->integer('BloqueaLibroObra');
            $table->integer('entry_by');
            $table->integer('updated_by');
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
        Schema::drop('tbl_contrato_estatus');
    }
}
