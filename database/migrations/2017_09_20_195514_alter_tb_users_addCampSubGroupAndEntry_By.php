<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTbUsersAddCampSubGroupAndEntryBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_users', function (Blueprint $table) {
            $table->integer('subgroup_id')->default('0');
            $table->integer('entry_by')->nulleable(true);
            $table->integer('entry_by_access')->nulleable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_users', function (Blueprint $table) {
            $table->dropcolumn('subgroup_id');
            $table->dropcolumn('entry_by');
            $table->dropcolumn('entry_by_access');
        });
    }
}
