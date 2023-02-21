<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtrosAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_otros_anexos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('anexo');
            $table->integer('estatus')->comment('habilitado = 1, deshabilitado = 2')->nullable();
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
        Schema::drop('tbl_otros_anexos');
    }
}
