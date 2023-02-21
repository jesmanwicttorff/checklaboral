<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAccesosDispositivoApp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tbl_accesos_dispositivo_app', function (Blueprint $table) {
          $table->increments('id');
          $table->string('identity');
          $table->string('type');
          $table->string('user');
          $table->string('device');
          $table->string('typeAccess')->nullable();
          $table->string('statusPerson');
          $table->string('statusAccess');
          $table->string('autorizationUser')->nullable();
          $table->string('observation');
          $table->string('reason');
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
        Schema::drop('tbl_accesos_dispositivo_app');
    }
}
