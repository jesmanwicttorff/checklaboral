<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblTicketsSubtiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_tickets_subtipos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre',128);
            $table->string('descripcion',128);
            $table->integer('entry_by');
            $table->integer('update_by');
            $table->integer('IdEstatus');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_tickets_subtipos');
    }
}
