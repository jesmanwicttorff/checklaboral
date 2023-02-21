<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratoEdpMandatoryFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_contrato_edp_mandatory_files",function(Blueprint $table){
          $table->increments('mandatory_id');
          $table->integer('edp_id');
          $table->string('filename',100);
          $table->date('created_at');
          $table->date('updated_at');
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
