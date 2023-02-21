<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedDocumentosestatusAnulado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('tbl_documentos_estatus')->insert(
            ["IdEstatus"=>"9","Descripcion"=>"Anulado", "Visible"=>"1"]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_documentos_estatus')->where('Descripcion', '=', 'Anulado')->delete();
    }
}
