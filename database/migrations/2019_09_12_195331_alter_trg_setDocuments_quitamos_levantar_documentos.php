<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTrgSetDocumentsQuitamosLevantarDocumentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `setDocuments`;");
        DB::unprepared("CREATE TRIGGER `setDocuments` AFTER INSERT ON `tbl_contratos_personas` FOR EACH ROW
                        BEGIN
                            DECLARE int_entry_by int;
                            IF IFNULL(NEW.entry_by_access,0) = 0 THEN
                                SELECT entry_by_access
                                INTO int_entry_by
                                FROM tbl_contratistas
                                WHERE tbl_contratistas.IdContratista = NEW.IdContratista;
                            ELSE 
                                SET int_entry_by := NEW.entry_by_access;
                            END IF;
                            UPDATE tbl_personas
                            SET entry_by_access = NEW.entry_by_access
                            WHERE tbl_personas.IdPersona = NEW.IdPersona;
                        END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        DB::unprepared("DROP TRIGGER IF EXISTS `setDocuments`;");
        DB::unprepared("CREATE TRIGGER `setDocuments` AFTER INSERT ON `tbl_contratos_personas` FOR EACH ROW
                        BEGIN

 

                            declare int_entry_by int;



                            IF IFNULL(NEW.entry_by_access,0) = 0 THEN

                            select entry_by_access

                            into int_entry_by

                            from tbl_contratistas

                            where tbl_contratistas.IdContratista = NEW.IdContratista;

                            ELSE 

                                set int_entry_by := NEW.entry_by_access;

                            END IF;



                            UPDATE tbl_personas

                            SET entry_by_access = NEW.entry_by_access

                            WHERE tbl_personas.IdPersona = NEW.IdPersona;



                            INSERT INTO tbl_documentos (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,

                                                            `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,

                                                            `FechaEmision`,`Resultado`,`contrato_id`, `IdContratista`)

                            SELECT NULL as IdDocumento,

                                tbl_requisitos.IdRequisito,

                                tbl_requisitos.IdTipoDocumento,

                                3 as Entidad,

                                NEW.IdPersona as IdEntidad,

                                NULL as Documento,

                                NULL as DocumentoURL,

                                NULL as DocumentoTexto,

                                NULL as FechaVencimiento,

                                1 IdEstatus,

                                now() as createdOn,

                                NEW.entry_by,

                                NEW.entry_by_access,

                                null as updatedOn,

                                NEW.FechaInicioFaena as FechaEmision,

                                NULL as Resultado,

                                NEW.contrato_id,

                                NEW.IdContratista

                            FROM tbl_requisitos

                            INNER JOIN tbl_requisitos_detalles ON tbl_requisitos.IdRequisito = tbl_requisitos_detalles.IdRequisito AND tbl_requisitos_detalles.IdEntidad = NEW.IdRol

                            WHERE tbl_requisitos.Entidad = 4

                            AND NOT EXISTS (SELECT * FROM tbl_documentos

                                            WHERE tbl_documentos.IdTipoDocumento = tbl_requisitos.IdTipoDocumento

                                            AND tbl_documentos.Entidad = 3

                                            AND tbl_documentos.IdEntidad = NEW.IdPersona

                                            AND tbl_documentos.contrato_id = NEW.contrato_id)

                            UNION ALL

                            SELECT NULL as IdDocumento,

                                tbl_requisitos.IdRequisito,

                                tbl_requisitos.IdTipoDocumento,

                                3 as Entidad,

                                NEW.IdPersona as IdEntidad,

                                NULL as Documento,

                                NULL as DocumentoURL,

                                NULL as DocumentoTexto,

                                NULL as FechaVencimiento,

                                1 IdEstatus,

                                now() as createdOn,

                                NEW.entry_by,

                                int_entry_by,

                                null as updatedOn,

                                NEW.FechaInicioFaena as FechaEmision,

                                NULL as Resultado,

                                NEW.contrato_id,

                                NEW.IdContratista

                            FROM tbl_requisitos

                            INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_requisitos.IdTipoDocumento

                            WHERE tbl_requisitos.Entidad = 3

                            AND tbl_tipos_documentos.IdProceso NOT IN (2,8,64)

                            AND NOT EXISTS (SELECT * FROM tbl_documentos

                                            WHERE tbl_documentos.IdTipoDocumento = tbl_requisitos.IdTipoDocumento

                                            AND tbl_documentos.Entidad = 3

                                            AND tbl_documentos.IdEntidad = NEW.IdPersona

                                            AND tbl_documentos.contrato_id = NEW.contrato_id)

                            

                            UNION ALL

                            SELECT NULL as IdDocumento,

                                0 as IdRequisito,

                                tbl_tipos_documentos.IdTipoDocumento,

                                3 as Entidad,

                                NEW.IdPersona as IdEntidad,

                                NULL as Documento,

                                NULL as DocumentoURL,

                                NULL as DocumentoTexto,

                                NULL as FechaVencimiento,

                                7 IdEstatus,

                                now() as createdOn,

                                NEW.entry_by,

                                int_entry_by,

                                null as updatedOn,

                                NEW.FechaInicioFaena as FechaEmision,

                                NULL as Resultado,

                                NEW.contrato_id,

                                NEW.IdContratista

                            FROM tbl_tipos_documentos

                            WHERE tbl_tipos_documentos.IdProceso = 8

                            AND NOT EXISTS (SELECT * FROM tbl_documentos

                                            WHERE tbl_documentos.IdTipoDocumento = tbl_tipos_documentos.IdTipoDocumento

                                            AND tbl_documentos.Entidad = 3

                                            AND tbl_documentos.IdEntidad = NEW.IdPersona

                                            AND tbl_documentos.contrato_id = NEW.contrato_id)

                            AND EXISTS (SELECT 1

                                        FROM tbl_documentos

                                        WHERE tbl_documentos.contrato_id = NEW.contrato_id

                                        AND tbl_documentos.Entidad = 2

                                        AND tbl_documentos.IdTipoDocumento = 7)

                            UNION ALL

                            SELECT NULL AS IdDocumento,

                                0 AS IdRequisito,

                                tbl_tipos_documentos.IdTipoDocumento,

                                3 AS Entidad,

                                NEW.IdPersona AS IdEntidad,

                                NULL AS Documento,

                                NULL AS DocumentoURL,

                                NULL AS DocumentoTexto,

                                NULL AS FechaVencimiento,

                                1 IdEstatus,

                                NOW() AS createdOn,

                                NEW.entry_by,

                                int_entry_by,

                                NULL AS updatedOn,

                                NEW.FechaInicioFaena AS FechaEmision,

                                NULL AS Resultado,

                                NEW.contrato_id,

                                NEW.IdContratista

                            FROM tbl_tipos_documentos

                            WHERE tbl_tipos_documentos.IdProceso = 64

                            AND NOT EXISTS (SELECT * FROM tbl_documentos

                                            WHERE tbl_documentos.IdTipoDocumento = tbl_tipos_documentos.IdTipoDocumento

                                            AND tbl_documentos.Entidad = 3

                                            AND tbl_documentos.IdEntidad = NEW.IdPersona

                                            AND tbl_documentos.contrato_id = NEW.contrato_id)

                            AND EXISTS (SELECT  tbl_personas.*

                                        FROM tbl_personas

                                        WHERE tbl_personas.IdPersona = NEW.IdPersona

                                        AND tbl_personas.id_Nac not in (21,22));                

                            END");

    }
}
