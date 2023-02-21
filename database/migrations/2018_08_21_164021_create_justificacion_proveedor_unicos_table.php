<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJustificacionProveedorUnicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('justificacion_proveedor_unicos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre',128);
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
        Schema::drop('justificacion_proveedor_unicos');
    }
}
