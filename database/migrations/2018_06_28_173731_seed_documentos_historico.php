<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedDocumentosHistorico extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $consulta = "SELECT *
                     FROM tbl_documentos
                     WHERE tbl_documentos.Entidad='3' and tbl_documentos.IdEstatus='5'
                     AND NOT EXISTS ( SELECT 1
                                      FROM tbl_contratos_personas
                                      WHERE tbl_contratos_personas.IdPersona = tbl_documentos.IdEntidad
                                      AND tbl_contratos_personas.contrato_id = tbl_documentos.contrato_id )
                      union
                     SELECT *
                        FROM tbl_documentos
                        WHERE tbl_documentos.entidad = 3
                        and tbl_documentos.IdTipoDocumento = 49
                        and tbl_documentos.IdEstatus = 5
                        and not exists ( select 1
                                         FROM tbl_contratos_personas
                                         WHERE tbl_contratos_personas.idrol = 2 
                                         and tbl_contratos_personas.IdPersona = tbl_documentos.IdEntidad)
                    union
                     SELECT *
                        FROM tbl_documentos
                        WHERE tbl_documentos.entidad = 3
                        and tbl_documentos.IdTipoDocumento = 4
                        and tbl_documentos.IdEstatus = 5;";



        $lobjData = \DB::select($consulta);

        foreach ($lobjData as $lisData) {

            \DB::table('tbl_documentos_rep_historico')->insert(
                ['IdDocumento'=> $lisData->IdDocumento,'IdTipoDocumento' => $lisData->IdTipoDocumento,
                    'Entidad' => $lisData->Entidad,'IdEntidad' => $lisData->IdEntidad,
                    'Documento' => $lisData->Documento,'DocumentoURL' => $lisData->DocumentoURL,
                    'FechaEmision' => $lisData->Documento,'FechaAprobacion' => new \DateTime(),
                    'FechaVencimiento' => $lisData->FechaVencimiento,'IdEstatus' => $lisData->IdEstatus,
                    'IdEstatusDocumento' => $lisData->IdEstatusDocumento,'Resultado' => $lisData->Resultado,
                    'load_by'=> $lisData->entry_by, 'approv_by' => $lisData->entry_by_access,
                    'contrato_id' => $lisData->contrato_id, 'IdContratista' => $lisData->IdContratista]);

            $lobjDataDocumentoV = \DB::table('tbl_documento_valor')->where('IdDocumento', '=', $lisData->IdDocumento)->get();

            foreach ($lobjDataDocumentoV as $lisData) {
                \DB::table('tbl_documento_valor_historico')->insert(
                    ['IdDocumento' => $lisData->IdDocumento, 'IdTipoDocumentoValor' => $lisData->IdTipoDocumentoValor,
                        'Valor' => $lisData->Valor, 'idCargado' => $lisData->idCargado,
                        'entry_by' => $lisData->entry_by, 'entry_by_access' => $lisData->entry_by_access]);

                \DB::table('tbl_documento_valor')->where('IdDocumento', '=', $lisData->IdDocumento)->delete();
            }

            $lobjDataBitacora = \DB::table('tbl_documentos_log')->where('IdDocumento', '=', $lisData->IdDocumento)->get();

            foreach ($lobjDataBitacora as $lisData) {
                \DB::table('tbl_documentos_log_historico')->insert(
                    ['IdDocumento'=> $lisData->IdDocumento, 'IdAccion'=> $lisData->IdAccion,
                        'DocumentoURL' => $lisData->DocumentoURL, 'observaciones' => $lisData->observaciones,
                        'entry_by' => $lisData->entry_by, 'createdOn'=> $lisData->createdOn]);

                \DB::table('tbl_documentos_log')->where('IdDocumento', '=', $lisData->IdDocumento)->delete();
            }

            \DB::table('tbl_documentos')->where('IdDocumento', '=', $lisData->IdDocumento)->delete();
        }
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
