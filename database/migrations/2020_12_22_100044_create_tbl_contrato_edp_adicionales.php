<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoEdpAdicionales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_edp_adicionales",function(Blueprint $table){
          $table->increments('adicional_id');
          $table->integer('edp_id');
          $table->integer('motivo_id');
          $table->decimal('monto',18,2);
          $table->decimal('porcentaje',18,2);
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
