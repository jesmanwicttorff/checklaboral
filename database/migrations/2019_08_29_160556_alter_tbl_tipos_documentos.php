<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblTiposDocumentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        \DB::table('tbl_tipos_documentos')->where('IdTipoDocumento',118)->update(['IdProceso'=>'118']);
        \DB::table('tbl_tipos_documentos')->where('IdTipoDocumento',117)->update(['IdProceso'=>'117']);
        \DB::table('tbl_tipos_documentos')->where('IdTipoDocumento',115)->update(['IdProceso'=>'115']);

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
