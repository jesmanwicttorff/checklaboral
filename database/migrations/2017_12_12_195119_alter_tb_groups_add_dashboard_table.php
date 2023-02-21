<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTbGroupsAddDashboardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_groups', function (Blueprint $table) {
            $table->integer('IdDashboard');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_groups', function (Blueprint $table) {
            $table->dropcolumn('IdDashboard');
        });
    }
}
