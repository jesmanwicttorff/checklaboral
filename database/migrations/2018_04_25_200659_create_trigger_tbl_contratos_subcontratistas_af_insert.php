<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerTblContratosSubcontratistasAfInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_contratos_subcontratistas_AFTER_INSERT`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `tbl_contratos_subcontratistas_AFTER_INSERT` 
                        AFTER INSERT ON `tbl_contratos_subcontratistas` FOR EACH ROW
                        BEGIN
                          
                          DECLARE lintEntryBy INT;
                          DECLARE lintIdContratista INT;

                           SELECT entry_by_access
                           INTO lintEntryBy
                           FROM tbl_contratistas
                           WHERE IdContratista = NEW.IdSubContratista;
                           
                            SELECT IdContratista
                           INTO lintIdContratista
                           FROM tbl_contrato
                           WHERE contrato_id = NEW.contrato_id;

            
                                INSERT INTO tbl_documentos (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                                 `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                                 `FechaEmision`,`Resultado`,`contrato_id`, `IdContratista`)
                                SELECT NULL as IdDocumento,
                                     tbl_requisitos.IdRequisito,
                                     IdTipoDocumento,
                                     9 as Entidad,
                                     NEW.IdSubContratista as IdEntidad,
                                     NULL as Documento,
                                     NULL as DocumentoURL,
                                     NULL as DocumentoTexto,
                                     NULL as FechaVencimiento,
                                     1 IdEstatus,
                                     now() as createdOn,
                                     tbl_requisitos.entry_by,
		                            lintEntryBy,
                                     null as updatedOn,
                                     NULL as FechaEmision,
                                     NULL as Resultado,
                                     NEW.contrato_id,
                                    lintIdContratista
                                FROM tbl_requisitos
                                WHERE tbl_requisitos.Entidad = 9
                               AND NOT EXISTS (SELECT * FROM tbl_documentos
                                            WHERE tbl_documentos.IdRequisito = tbl_requisitos.IdRequisito
                                            AND tbl_documentos.Entidad = 9
                                            AND tbl_documentos.IdEntidad = NEW.IdSubContratista
                                            AND tbl_documentos.contrato_id = NEW.contrato_id);
                                            
                        END
                       ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_contratos_subcontratistas_AFTER_INSERT`;");
    }
}
