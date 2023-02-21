<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoEdpMovimientos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_edp_movimientos",function(Blueprint $table){
          $table->increments('mov_id');
          $table->integer('edp_id')->index();
          $table->integer('user_id')->index();
          $table->string('movimiento',250);
          $table->dateTime('created_at');
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
