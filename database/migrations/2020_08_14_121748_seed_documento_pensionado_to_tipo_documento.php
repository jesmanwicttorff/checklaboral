<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedDocumentoPensionadoToTipoDocumento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $id = DB::table('tbl_tipos_documentos')->insertGetId([
            'IdFormato' => 'application/pdf',
            'group_id' => 0,
            'Entidad' => 3,
            'IconoFormato' => NULL,
            'Descripcion' => 'Certificado exención de cotizar.',
            'nombre_archivo' => 'certificado_excencion_cotizar',
            'ControlCheckLaboral' => 0,
            'TextoExplicativo' => 'Certificado exención de cotizar',
            'Permanencia' => 2,
            'Vigencia' => 3,
            'Periodicidad' => 0,
            'Formula' => NULL,
            'BloqueaAcceso' => 'NO',
            'Acreditacion' => 0,
            'tipo' => 1,
            'MultipleDocumentos' => 0,
            'IdProceso' => 143,
            'DiasVencimiento' => 0,
            'createdOn' => date('Y-m-d H:i:s'),
            'entry_by' => 1,
            'updatedOn' => date('Y-m-d H:i:s'),
            'entry_by_access' => NULL,
            'Ponderado' => 0,
            'RelacionPersona' => 0
        ]);

        DB::table('tbl_tipo_documento_perfil')->insert([

            'idTipoDocumento' => $id,
            'idUsuario' => 1,
            'IdPerfil' => 1
        ]);
        DB::table('tbl_tipo_documento_perfil')->insert([

            'idTipoDocumento' => $id,
            'idUsuario' => 1,
            'IdPerfil' => 6
        ]);

        DB::table('tbl_perfil_aprobacion')->insert([

            'idTipoDocumento' => $id,
            'group_id' => 1,
            'entry_by' => 1,
            'createdOn' => date('Y-m-d H:i:s')
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
        DB::table('tbl_tipos_documentos')->where('IdProceso', 143)->delete();
    }
}
