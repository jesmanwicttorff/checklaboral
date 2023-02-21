<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblHistorialAcreditacionActivos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tbl_historial_acreditacion_activos', function (Blueprint $table) {
          $table->increments('id');
          $table->string('numero',50);
          $table->date('acreditacion');
          $table->integer('entry_by');
          $table->integer('IdEstatus');
          $table->timestamp('fecha');
          $table->integer('contrato_id');
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
