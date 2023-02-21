<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMotivoRechazoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_motivo_rechazo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('Descripcion',128);
            $table->integer('IdEstatus')->default('1');
            $table->timestamp('createdOn')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('entry_by');
            $table->timestamp('updatedOn')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_motivo_rechazo');
    }
}
