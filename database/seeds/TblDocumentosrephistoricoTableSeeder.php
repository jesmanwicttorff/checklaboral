<?php

use Illuminate\Database\Seeder;

class TblDocumentosrephistoricoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listadoData = \DB::table('tbl_documentos')->select('tbl_documentos.IdTipoDocumento','tbl_documentos.Entidad','IdEntidad','Documento',
            'DocumentoURL','updatedOn','FechaVencimiento','IdEstatusDocumento','tbl_documentos.entry_by','tbl_documentos.entry_by_access','contrato_id','IdContratista')
            ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento","=","tbl_tipos_documentos.IdTipoDocumento")
            ->where("tbl_documentos.Entidad","=","3")
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('tbl_contratos_personas')
                    ->whereRaw('tbl_contratos_personas.IdPersona = tbl_documentos.IdEntidad');
            })
            ->where("IdEstatus","=","5")
            ->get();


        foreach ($listadoData as $lisData) {
            DB::table('tbl_documentos_rep_historico')->insert([
                ['IdTipoDocumento' => $lisData->IdTipoDocumento, 'Entidad' => $lisData->Entidad,
                    'IdEntidad' => $lisData->IdEntidad, 'Documento' => $lisData->Documento,
                    'DocumentoURL' => $lisData->DocumentoURL, 'FechaAprobacion' => $lisData->updatedOn,
                    'FechaVencimiento' => $lisData->FechaVencimiento, 'IdEstatusDocumento' => $lisData->IdEstatusDocumento,
                    'load_by' => $lisData->entry_by, 'approv_by' => $lisData->entry_by_access,
                    'contrato_id' => $lisData->contrato_id, 'IdContratista' => $lisData->IdContratista]]);
        }
    }
}
