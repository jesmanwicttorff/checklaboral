<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblIndicadoresColorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_indicadores_color', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('indicador_id');
            $table->integer('desde');
            $table->integer('hasta');
            $table->string('color');
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
        Schema::drop('tbl_indicadores_color');
    }
}
