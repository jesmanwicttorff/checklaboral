<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTiposContratosPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_tipos_contratos_personas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('Nombre',128);
            $table->string('Descripcion',255);
            $table->boolean('Vencimiento');
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
        Schema::drop('tbl_tipos_contratos_personas');
    }
}
