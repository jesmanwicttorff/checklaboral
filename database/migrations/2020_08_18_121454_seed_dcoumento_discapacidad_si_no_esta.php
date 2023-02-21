<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedDcoumentoDiscapacidadSiNoEsta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $disc = DB::table('tbl_tipos_documentos')->where('IdProceso', 142)->first();
    
        if(!$disc){

            $id = DB::table('tbl_tipos_documentos')->insertGetId([
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
        }else{
            $id = $disc->IdTipoDocumento;
        }
        $perfil = DB::table('tbl_tipo_documento_perfil')
        ->where('idTipoDocumento',$id)
        ->where('idUsuario', 1)
        ->where('IdPerfil', 1)
        ->first();

        if(!$perfil){
            DB::table('tbl_tipo_documento_perfil')->insert([
                'idTipoDocumento' => $id,
                'idUsuario' => 1,
                'IdPerfil' => 1
            ]);
        }

        $perfil2 = DB::table('tbl_tipo_documento_perfil')
        ->where('idTipoDocumento',$id)
        ->where('idUsuario', 1)
        ->where('IdPerfil', 6)
        ->first();

        if(!$perfil2){
            DB::table('tbl_tipo_documento_perfil')->insert([
                'idTipoDocumento' => $id,
                'idUsuario' => 1,
                'IdPerfil' => 6
            ]);
        }
        $aprobacion = DB::table('tbl_perfil_aprobacion')
        ->where('idTipoDocumento',$id)
        ->where('group_id',1)
        ->first();
        if(!$aprobacion){

            DB::table('tbl_perfil_aprobacion')->insert([
                'idTipoDocumento' => $id,
                'group_id' => 1,
                'entry_by' => 1,
                'createdOn' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $id = DB::table('tbl_tipos_documentos')->where('IdProceso', 142)->first();
        $id = $id->IdTipoDocumento;

        $perfil = DB::table('tbl_tipo_documento_perfil')
        ->where('idTipoDocumento',$id)
        ->whereIn('IdPerfil', [1,6])
        ->delete();

        $aprobacion = DB::table('tbl_perfil_aprobacion')
        ->where('idTipoDocumento',$id)
        ->where('group_id',1)
        ->delete();

        //DB::table('tbl_tipos_documentos')->where('IdProceso', 142)->delete();

    }
    
}
