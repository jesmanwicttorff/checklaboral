<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblContratoItemizadoLineas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_itemizado_lineas",function(Blueprint $table){
          $table->increments('linea_id');
          $table->integer('itemizado_id');
          $table->integer('tipoCobro_id');
          $table->decimal('montoLimite');
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
