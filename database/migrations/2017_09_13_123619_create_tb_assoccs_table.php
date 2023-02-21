<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbAssoccsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_assocc', function (Blueprint $table) {
            $table->increments('idAssocc');
            $table->integer('idAssoccGroup');
            $table->integer('user_id');
            $table->integer('contrato_id');
            $table->integer('contratista_id');
            $table->integer('entry_by');
            $table->integer('entry_by_access');
            $table->foreign('user_id')->references('id')->on('tb_users');
            $table->foreign('contrato_id')->references('contrato_id')->on('tbl_contrato');
            $table->foreign('contratista_id')->references('IdContratista')->on('tbl_contratistas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tb_assocc');
    }
}
