<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAccesoDispositivo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tbl_accesos_dispositivo', function (Blueprint $table) {
          $table->increments('id');
          $table->string('rut',20);
          $table->enum('acceso',['entrada','salida']);
          $table->dateTime('fecha_marca');
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
        Schema::drop('tbl_accesos_dispositivo');
    }
}
