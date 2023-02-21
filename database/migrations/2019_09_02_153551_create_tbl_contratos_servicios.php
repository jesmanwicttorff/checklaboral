<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratosServicios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contratos_servicios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idgrupoespecifico');
            $table->string('name');
            $table->integer('idestatus');
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
        Schema::drop('tbl_contratos_servicios');
    }
}
