<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ActivatestatusvencidoDocumentosEstatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('tbl_documentos_estatus')
            ->where('Descripcion', '=', 'Vencido')
            ->update(['Visible' => '1']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_documentos_estatus')
            ->where('Descripcion', '=', 'Vencido')
            ->update(['Visible' => '0']);
    }
}
