<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTipodocumentoSubcontratista extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idTipo = \DB::table('tbl_tipos_documentos')->insertGetId(
            ['IdFormato' => 'application/pdf', 'group_id' => 0, 'Entidad' => 9, 'Descripcion' => 'Carta de aceptación de subcontratista', 'nombre_archivo' => 'Carta de aceptación de subcontratista', 'Permanencia' => '1', 'Vigencia' => '1', 'Periodicidad' => '3', 'BloqueaAcceso' => 'SI', 'Tipo' => '1']
        );

        \DB::table('tbl_tipo_documento_perfil')->insert(
            ['idTipoDocumento' => $idTipo, 'idUsuario' => '1', 'IdPerfil' => '6']
        );

        \DB::table('tbl_perfil_aprobacion')->insert(
            ['idTipoDocumento' => $idTipo, 'group_id' => '1', 'entry_by' => '1'],
            ['idTipoDocumento' => $idTipo, 'group_id' => '4', 'entry_by' => '1']
        );
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
