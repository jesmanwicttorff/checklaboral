<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCharlaSeguridadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_charla_seguridad', function (Blueprint $table) {
            $table->increments('id');
            $table->string('IdTipoIdentificacion',50);
            $table->string('RUT',50);
            $table->date('FechaAsistencia');
            $table->date('FechaVencimiento');
            $table->integer('entry_by');
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
        Schema::drop('tbl_charla_seguridad');
    }
}
