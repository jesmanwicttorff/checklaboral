<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoEdpSublineas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_edp_sublineas",function(Blueprint $table){
          $table->increments('edp_sublinea_id');
          $table->integer('linea_id');
          $table->decimal('cantidad',10,0);
          $table->decimal('montoLinea',10,0);
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
