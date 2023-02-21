<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblGroupsLevelsAssocContract extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_groups_levels_assoc_contract",function(Blueprint $table){
        //$table->integer('group_id')->index();
        $table->integer('level')->index();
        $table->integer('contrato_id')->index();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::drop("tbl_groups_levels_assoc_contract");
    }
}
