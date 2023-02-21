<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedDocumentoDiscapacidadToTiposDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::table('tbl_tipos_documentos')->insert([
            'IdFormato' => 'application/pdf',
            'group_id' => 0,
            'Entidad' => 3,
            'IconoFormato' => NULL,
            'Descripcion' => 'Credencial de Discapacidad',
            'nombre_archivo' => 'credencial_discapacidad',
            'ControlCheckLaboral' => 0,
            'TextoExplicativo' => 'Credencial para personas discapacitadas',
            'Permanencia' => 2,
            'Vigencia' => 3,
            'Periodicidad' => 0,
            'Formula' => NULL,
            'BloqueaAcceso' => 'NO',
            'Acreditacion' => 0,
            'tipo' => 1,
            'MultipleDocumentos' => 0,
            'IdProceso' => 142,
            'DiasVencimiento' => 0,
            'createdOn' => date('Y-m-d H:i:s'),
            'entry_by' => 1,
            'updatedOn' => date('Y-m-d H:i:s'),
            'entry_by_access' => NULL,
            'Ponderado' => 0,
            'RelacionPersona' => 0
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::table('tbl_tipos_documentos')->where('IdProceso', 142)->delete();
    }
}
