<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblF301Discrepancias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_f301_discrepancias",function(Blueprint $table){
          $table->increments('discrepancia_id');
          $table->date('periodo');
          $table->decimal('valor',10,2);
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
