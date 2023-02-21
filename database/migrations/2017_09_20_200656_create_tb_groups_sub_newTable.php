<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbGroupsSubNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_groups_sub', function (Blueprint $table) {
            $table->increments('subgroup_id');
            $table->integer('group_id');
            $table->string('name',30);
            $table->string('description',50);
            $table->integer('level');
            $table->integer('entry_by');
            $table->integer('entry_by_access');
            #$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tb_groups_sub');
    }
}
