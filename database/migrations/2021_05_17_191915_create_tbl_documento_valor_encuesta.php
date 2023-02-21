<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDocumentoValorEncuesta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_documento_valor_encuesta",function(Blueprint $table){
          $table->integer('encuesta_id')->index();
          $table->integer('IdDocumento')->index();
          $table->text('observacion');
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
