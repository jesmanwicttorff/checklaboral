<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoCentrotrabajo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_centrotrabajo",function(Blueprint $table){
          $table->increments('contratoct_id');
          $table->integer('contrato_id')->index();
          $table->integer('uen_ct_id')->index();
          $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("tbl_contrato_centrotrabajo");
    }
}
