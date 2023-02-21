<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblContratoItemizado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table("tbl_contrato_itemizado",function(Blueprint $table){
          $table->increments('itemizado_id');
          $table->integer('contrato_id');
          $table->integer('moneda_id');
          $table->integer('condicionPago_id');
          $table->string('tiposDocumentos_id');
          $table->enum('tipoLinea',['unica','multiple']);
          $table->decimal('MontoTotal',10,0);
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
