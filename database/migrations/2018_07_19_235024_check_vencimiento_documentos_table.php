<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CheckVencimientoDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /*
         *  Procedimiento que verifica los documentos que tienen vencimiento y les activa su campo en la tabla tbl_documentos
         * */
        \DB::table('tbl_documentos')
            ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento","=", "tbl_tipos_documentos.IdTipoDocumento")
            ->where('tbl_tipos_documentos.Vigencia','=',1)
            ->update(['tbl_documentos.Vencimiento' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_documentos')
            ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento","=", "tbl_tipos_documentos.IdTipoDocumento")
            ->where('tbl_tipos_documentos.Vigencia','=',1)
            ->update(['tbl_documentos.Vencimiento' => 0]);
    }
}
