<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTrgRequisitosInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_requisitos_AFTER_INSERT`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_requisitos_AFTER_INSERT`;");
        DB::unprepared("CREATE TRIGGER `tbl_requisitos_AFTER_INSERT` AFTER INSERT ON `tbl_requisitos` FOR EACH ROW 
                         BEGIN
                          IF NEW.Vigencia = 'Todos' THEN
                            
                            IF NEW.Entidad = 1 THEN
                             INSERT INTO tbl_documentos  (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                                                         `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                                                         `FechaEmision`,`Resultado`,`contrato_id`, `IdContratista`)
                             SELECT NULL as IdDocumento,
                                    tbl_requisitos.IdRequisito,
                                    IdTipoDocumento,
                                    1 as Entidad,
                                    tbl_contratistas.IdContratista as IdEntidad,
                                    NULL as Documento,
                                    NULL as DocumentoURL,
                                    NULL as DocumentoTexto,
                                    NULL as FechaVencimiento,
                                    1 IdEstatus,
                                    now() as createdOn,
                                    tbl_requisitos.entry_by,
                                    tbl_contratistas.entry_by_access,
                                    null as updatedOn,
                                    NULL as FechaEmision,
                                    NULL as Resultado,
                                    NULL,
                                    tbl_contratistas.IdContratista
                               FROM tbl_requisitos
                               INNER JOIN (select tbl_contratistas.* from tbl_contratistas 
                                           where exists (select tbl_contrato.contrato_id from tbl_contrato where tbl_contratistas.IdContratista = tbl_contrato.IdContratista and tbl_contrato.cont_estado = 1)) as tbl_contratistas
                               WHERE tbl_requisitos.Entidad = 1
                               AND tbl_requisitos.IdRequisito = NEW.IdRequisito
                               AND NOT EXISTS (SELECT * FROM tbl_documentos
                                            WHERE tbl_documentos.IdRequisito = tbl_requisitos.IdRequisito
                                            AND tbl_documentos.Entidad = 1
                                            AND tbl_documentos.IdEntidad = tbl_contratistas.IdContratista);
                              
                              ELSEIF NEW.Entidad = 2 THEN
                                INSERT INTO tbl_documentos (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                                                         `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                                                         `FechaEmision`,`Resultado`,`contrato_id`, `IdContratista`)
                             SELECT NULL as IdDocumento,
                                    tbl_requisitos.IdRequisito,
                                    IdTipoDocumento,
                                    2 as Entidad,
                                    tbl_contrato.contrato_id as IdEntidad,
                                    NULL as Documento,
                                    NULL as DocumentoURL,
                                    NULL as DocumentoTexto,
                                    NULL as FechaVencimiento,
                                    1 IdEstatus,
                                    now() as createdOn,
                                    tbl_requisitos.entry_by,
                                    tbl_contrato.entry_by_access,
                                    null as updatedOn,
                                    NULL as FechaEmision,
                                    NULL as Resultado,
                                    tbl_contrato.contrato_id,
                                    NULL
                               FROM tbl_requisitos
                               INNER JOIN tbl_contrato ON tbl_contrato.cont_estado = 1
                               WHERE tbl_requisitos.Entidad = 2
                               AND tbl_requisitos.IdRequisito = NEW.IdRequisito
                               AND NOT EXISTS (SELECT * FROM tbl_documentos
                                            WHERE tbl_documentos.IdRequisito = tbl_requisitos.IdRequisito
                                            AND tbl_documentos.Entidad = 2
                                            AND tbl_documentos.IdEntidad = tbl_contrato.contrato_id);
                            ELSEIF NEW.Entidad = 3 THEN
                            if NEW.IdTipoDocumento = 64 then
                            
                                INSERT INTO tbl_documentos (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                                                         `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                                                         `FechaEmision`,`Resultado`,`contrato_id`, `IdContratista`)
                                SELECT NULL as IdDocumento,
                                    tbl_requisitos.IdRequisito,
                                    IdTipoDocumento,
                                    3 as Entidad,
                                    tbl_personas.IdPersona as IdEntidad,
                                    NULL as Documento,
                                    NULL as DocumentoURL,
                                    NULL as DocumentoTexto,
                                    NULL as FechaVencimiento,
                                    1 IdEstatus,
                                    now() as createdOn,
                                    tbl_requisitos.entry_by,
                                    tbl_personas.entry_by_access,
                                    null as updatedOn,
                                    NULL as FechaEmision,
                                    NULL as Resultado,
                                    tbl_contrato.contrato_id,
                                    NULL
                                    FROM tbl_requisitos
                                INNER JOIN tbl_contrato ON tbl_contrato.cont_estado = 1
                                INNER JOIN tbl_contratos_personas ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                                INNER JOIN tbl_personas ON tbl_contratos_personas.IdPersona = tbl_personas.IdPersona
                                WHERE tbl_requisitos.Entidad = 3
                                and tbl_personas.id_Nac not in (21,22)
                                AND tbl_requisitos.IdRequisito = NEW.IdRequisito
                                    AND NOT EXISTS (SELECT * FROM tbl_documentos
                                            WHERE tbl_documentos.IdRequisito = tbl_requisitos.IdRequisito
                                            AND tbl_documentos.Entidad = 3
                                            AND tbl_documentos.IdEntidad = tbl_personas.IdPersona);
                            else
                                INSERT INTO tbl_documentos (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                                                         `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                                                         `FechaEmision`,`Resultado`,`contrato_id`, `IdContratista`)
                                SELECT NULL AS IdDocumento,
                                    tbl_requisitos.IdRequisito,
                                    IdTipoDocumento,
                                    3 AS Entidad,
                                    tbl_personas.IdPersona AS IdEntidad,
                                    NULL AS Documento,
                                    NULL AS DocumentoURL,
                                    NULL AS DocumentoTexto,
                                    NULL AS FechaVencimiento,
                                    1 IdEstatus,
                                    NOW() AS createdOn,
                                    tbl_requisitos.entry_by,
                                    tbl_personas.entry_by_access,
                                    NULL AS updatedOn,
                                    NULL AS FechaEmision,
                                    NULL AS Resultado,
                                    tbl_contrato.contrato_id,
                                    NULL
                                    FROM tbl_requisitos
                                INNER JOIN tbl_contrato ON tbl_contrato.cont_estado = 1
                                INNER JOIN tbl_contratos_personas ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                                INNER JOIN tbl_personas ON tbl_contratos_personas.IdPersona = tbl_personas.IdPersona
                                WHERE tbl_requisitos.Entidad = 3
                                AND tbl_requisitos.IdRequisito = NEW.IdRequisito
                                    AND NOT EXISTS (SELECT * FROM tbl_documentos
                                            WHERE tbl_documentos.IdRequisito = tbl_requisitos.IdRequisito
                                            AND tbl_documentos.Entidad = 3
                                            AND tbl_documentos.IdEntidad = tbl_personas.IdPersona);
                            
                            end if;
                              
                              elseif NEW.Entidad>11 then
                            INSERT INTO tbl_documentos  (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                                                         `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                                                         `FechaEmision`,`Resultado`,`contrato_id`, `IdContratista`)
                              SELECT NULL as IdDocumento,
                                    tbl_requisitos.IdRequisito,
                                    IdTipoDocumento,
                                    NEW.Entidad as Entidad,
                                    tbl_contrato.contrato_id as IdEntidad,
                                    NULL as Documento,
                                    NULL as DocumentoURL,
                                    NULL as DocumentoTexto,
                                    NULL as FechaVencimiento,
                                    1 IdEstatus,
                                    now() as createdOn,
                                    tbl_requisitos.entry_by,
                                    tbl_contrato.entry_by_access,
                                    null as updatedOn,
                                    NULL as FechaEmision,
                                    NULL as Resultado,
                                    tbl_contrato.contrato_id,
                                    NULL
                               FROM tbl_requisitos
                               INNER JOIN tbl_contrato ON tbl_contrato.cont_estado = 1
                               WHERE tbl_requisitos.Entidad = NEW.Entidad
                               AND tbl_requisitos.IdRequisito = NEW.IdRequisito
                               AND NOT EXISTS (SELECT * FROM tbl_documentos
                                            WHERE tbl_documentos.IdRequisito = tbl_requisitos.IdRequisito
                                            AND tbl_documentos.Entidad = NEW.Entidad
                                            AND tbl_documentos.IdEntidad = tbl_contrato.contrato_id);
                              END IF;
                              
                            END IF;
                            
                        END;");
    }
}
