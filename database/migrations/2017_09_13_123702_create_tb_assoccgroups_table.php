<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbAssoccgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_assoccgroup', function (Blueprint $table) {
            $table->increments('idAssoccGroup');
            $table->integer('group_id');
            $table->integer('subgroup_id');
            $table->integer('entry_by');
            $table->integer('entry_by_access');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tb_assoccgroup');
    }
}
