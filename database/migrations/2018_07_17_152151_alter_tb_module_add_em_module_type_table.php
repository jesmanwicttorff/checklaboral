<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTbModuleAddEmModuleTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        \DB::statement("ALTER TABLE `tb_module` CHANGE COLUMN `module_type` `module_type` ENUM('master', 'report', 'proccess', 'core', 'generic', 'addon', 'ajax', 'checklaboral') NULL DEFAULT 'master' ;");
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        \DB::statement("ALTER TABLE `tb_module` CHANGE COLUMN `module_type` `module_type` ENUM('master', 'report', 'proccess', 'core', 'generic', 'addon', 'ajax') NULL DEFAULT 'master' ;");

    }
}
