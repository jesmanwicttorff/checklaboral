<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedDocumentosestatusAnuladonvisible extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('tbl_documentos_estatus')
            ->where('Descripcion', '=', 'Anulado')
            ->update(['Visible' => '0']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_documentos_estatus')
            ->where('Descripcion', '=', 'Anulado')
            ->update(['Visible' => '1']);
    }
}
