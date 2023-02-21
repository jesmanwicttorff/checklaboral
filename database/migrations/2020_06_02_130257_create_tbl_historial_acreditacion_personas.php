<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblHistorialAcreditacionPersonas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tbl_historial_acreditacion_personas', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('IdPersona');
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
