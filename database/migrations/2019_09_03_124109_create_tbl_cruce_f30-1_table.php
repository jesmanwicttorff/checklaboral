<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCruceF301Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_cruce_f30_1', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('iddocumento');
            $table->date('periodo');
            $table->integer('idpersona')->nullable();
            $table->string('rut');
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
        Schema::drop('tbl_cruce_f30_1');
    }
}
