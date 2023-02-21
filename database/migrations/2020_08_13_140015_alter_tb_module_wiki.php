<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTbModuleWiki extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      \DB::table('tb_module')
          ->where('module_name','=','wikiandina')
          ->update(['module_name'=>'wiki']);

      \DB::table('tb_menu')
          ->where('module','=','wikiandina')
          ->update(['module'=>'wiki']);

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
