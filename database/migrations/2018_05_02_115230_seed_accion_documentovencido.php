<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedAccionDocumentovencido extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('tbl_acciones')->insert(
            ["Nombre"=>"Vencido", "Descripcion"=>"Se Vence el documento"]
        );

        \DB::table('tbl_acciones')
            ->where('Nombre',"Temporal")
            ->update(['Descripcion' => "Se coloca el documento como temporal"]);
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
