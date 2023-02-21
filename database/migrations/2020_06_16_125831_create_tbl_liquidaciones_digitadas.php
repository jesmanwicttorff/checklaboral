<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblLiquidacionesDigitadas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tbl_liquidaciones_digitadas', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('mes');
          $table->integer('ano');
          $table->string('cont_numero',25);
          $table->string('RUT',15);
          $table->integer('diasTrab');
          $table->integer('sueldoBase');
          $table->integer('gratif');
          $table->integer('nHe50');
          $table->integer('pHe50');
          $table->integer('nHe100');
          $table->integer('pHe100');
          $table->integer('totalImponible');
          $table->integer('totalHaberes');
          $table->integer('totalLiquido');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
