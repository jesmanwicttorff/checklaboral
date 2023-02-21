<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAccesosDispositivoAppCounters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tbl_accesos_dispositivo_app_counters', function (Blueprint $table) {
          $table->increments('id');
          $table->string('user');
          $table->string('device');
          $table->integer('countersOk');
          $table->integer('countersNok');
          $table->integer('counterOutOk');
          $table->dateTime('createdAt');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_accesos_dispositivo_app_counters');
    }
}
