<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableTblPensionadosSiNoExiste extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $base = DB::select('show tables like "tbl_pensionados"');
        if(!$base){
            Schema::create('tbl_pensionados', function (Blueprint $table) {
                $table->increments('id_pens');
                $table->integer('IdPersona');
                $table->integer('contrato_id')->nullable();
                $table->integer('IdContratista')->nullable();
                $table->dateTime('Fecha')->nullable();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
