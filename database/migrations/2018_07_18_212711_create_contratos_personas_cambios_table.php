<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContratosPersonasCambiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contratos_personas_cambios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('IdDocumento');
            $table->integer('IdPersona');
            $table->integer('IdContratoNuevo');
            $table->integer('IdRol');
            $table->integer('IdTipoContrato');
            $table->date('FechaVencimiento');
            $table->boolean('IdEstatus');
            $table->integer('entry_by');
            $table->integer('updated_by');
            $table->timestamp('createdOn');
            $table->timestamp('updatedOn');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_contratos_personas_cambios');
    }
}
