<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedAccionDocumentoanulado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('tbl_acciones')->insert(
            ["Nombre"=>"Anulado", "Descripcion"=>"Se Anula el documento"]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_acciones')->where('Nombre', '=', 'Anulado')->delete();
    }
}
