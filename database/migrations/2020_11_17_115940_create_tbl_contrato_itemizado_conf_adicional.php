<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoItemizadoConfAdicional extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_itemizado_conf_adicional",function(Blueprint $table){
          $table->increments('adicional_id');
          $table->integer('itemizado_id');
          $table->string('motivo_id');
          $table->decimal('monto',10,0);
          $table->string('type_id');
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
