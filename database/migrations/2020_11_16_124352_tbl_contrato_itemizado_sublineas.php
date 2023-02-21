<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblContratoItemizadoSublineas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_itemizado_sublineas",function(Blueprint $table){
          $table->increments('sublinea_id');
          $table->integer('linea_id');
          $table->string('nombre',120);
          $table->decimal('cantidad',10,0);
          $table->integer('unidadMedidad_id');
          $table->decimal('montoLinea',10,0);
          $table->enum('documentacion',[0,1]);
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
