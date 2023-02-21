<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratosAcreditacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contratos_acreditacion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contrato_id');
            $table->integer('entry_by');
            $table->date('acreditacion');
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
        Schema::drop('tbl_contratos_acreditacion');
    }
}
