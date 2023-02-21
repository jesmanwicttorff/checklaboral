<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerTblAccesoAreasAfInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_acceso_areas_AFTER_INSERT`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `tbl_acceso_areas_AFTER_INSERT` 
                        AFTER INSERT ON `tbl_acceso_areas` FOR EACH ROW
                        BEGIN
                          
                          DECLARE lintIdTipoAcceso INT;
                          DECLARE lintidContrato INT;
                          DECLARE lintEntryBy INT;
                          DECLARE lintIdPersona INT;
                          
                          SELECT tbl_accesos.IdTipoAcceso, tbl_accesos.contrato_id, tbl_contrato.entry_by_access, tbl_accesos.IdPersona
                          INTO lintIdTipoAcceso, lintidContrato, lintEntryBy, lintIdPersona
                          FROM tbl_accesos
                          INNER JOIN tbl_contrato ON tbl_accesos.contrato_id = tbl_contrato.contrato_id
                          WHERE tbl_accesos.IdAcceso = NEW.IdAcceso;
                          
                          IF (lintIdTipoAcceso = 1) THEN
                            
                                INSERT INTO tbl_documentos (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                                                         `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                                                         `FechaEmision`,`Resultado`,`contrato_id`)
                                SELECT NULL as IdDocumento,
                                     tbl_requisitos.IdRequisito,
                                     IdTipoDocumento,
                                     3 as Entidad,
                                     lintIdPersona as IdEntidad,
                                     NULL as Documento,
                                     NULL as DocumentoURL,
                                     NULL as DocumentoTexto,
                                     NULL as FechaVencimiento,
                                     1 IdEstatus,
                                     now() as createdOn,
                                     lintEntryBy,
                                     lintEntryBy,
                                     null as updatedOn,
                                     NULL as FechaEmision,
                                     NULL as Resultado,
                                     lintidContrato
                                FROM tbl_requisitos
                                INNER JOIN tbl_requisitos_detalles ON tbl_requisitos.IdRequisito = tbl_requisitos_detalles.IdRequisito AND tbl_requisitos_detalles.IdEntidad = NEW.IdAreaTrabajo
                                WHERE tbl_requisitos.Entidad = 5
                                AND NOT EXISTS (SELECT * FROM tbl_documentos
                                                WHERE tbl_documentos.IdTipoDocumento = tbl_requisitos.IdTipoDocumento
                                                AND tbl_documentos.Entidad = 3
                                                AND tbl_documentos.IdEntidad = lintIdPersona);
                                            
                          END IF;
                          
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
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_acceso_areas_AFTER_INSERT`;");
    }
}
