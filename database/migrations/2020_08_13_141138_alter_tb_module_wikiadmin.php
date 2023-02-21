<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTbModuleWikiadmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      \DB::table('tb_module')
          ->where('module_name','=','wikiandinaadmin')
          ->update(['module_name'=>'wikiadmin']);

      \DB::table('tb_menu')
          ->where('module','=','wikiandinaadmin')
          ->update(['module'=>'wikiadmin']);
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
