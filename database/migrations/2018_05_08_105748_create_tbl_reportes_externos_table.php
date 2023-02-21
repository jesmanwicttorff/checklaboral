<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblReportesExternosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_reportes_externos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre',128);
            $table->string('descripcion',128);
            $table->string('objeto',128);
            $table->integer('idperiodidicad');
            $table->integer('idsegmento');
            $table->integer('idtipo');
            $table->integer('idestatus');
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
        Schema::drop('tbl_reportes_externos');
    }
}
