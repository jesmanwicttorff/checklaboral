<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAccesosAgregoDatarutTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_accesos_BEFORE_INSERT`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `tbl_accesos_BEFORE_INSERT` BEFORE INSERT ON `tbl_accesos` FOR EACH ROW begin
                        DECLARE lintIdEstatus INT;
                        DECLARE lstrDataRut varchar(20);
                        DECLARE lstrDataNombres varchar(50);
                        DECLARE lstrDataApellidos varchar(50);
                            if new.IdTipoAcceso = 1 THEN
                                SET lintIdEstatus := (SELECT 2
                                                FROM tbl_personas
                                                INNER JOIN tbl_contratos_personas ON tbl_personas.IdPersona = tbl_contratos_personas.IdPersona
                                                INNER JOIN tbl_contrato ON tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id
                                                INNER JOIN tbl_contratistas ON tbl_contrato.IdContratista = tbl_contratistas.IdContratista
                                                WHERE tbl_personas.IdPersona = NEW.IdPersona
                                                AND (EXISTS (SELECT tbl_documentos.IdDocumento
                                                             FROM tbl_documentos
                                                             INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                             WHERE tbl_documentos.Entidad = 1
                                                             AND tbl_documentos.IdEntidad = tbl_contratistas.IdContratista
                                                             AND ( (tbl_documentos.IdEstatus NOT IN (4,5)) OR (tbl_documentos.IdEstatusDocumento NOT IN (1)) ) )
                                                  OR EXISTS (SELECT tbl_documentos.IdDocumento
                                                             FROM tbl_documentos
                                                             INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                             WHERE tbl_documentos.Entidad = 2
                                                             AND tbl_documentos.IdEntidad = tbl_contrato.contrato_id
                                                             AND ( (tbl_documentos.IdEstatus NOT IN (4,5)) OR (tbl_documentos.IdEstatusDocumento NOT IN (1)) ) )
                                                  OR EXISTS (SELECT tbl_documentos.IdDocumento
                                                             FROM tbl_documentos
                                                             INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                             WHERE tbl_documentos.Entidad = 3
                                                             AND tbl_documentos.IdEntidad = tbl_personas.IdPersona
                                                             AND ( (tbl_documentos.IdEstatus NOT IN (4,5)) OR (tbl_documentos.IdEstatusDocumento NOT IN (1)) ) ) ) );

                                IF lintIdEstatus = 2 THEN
                                    SET NEW.IdEstatus := 2;  
                                ELSE 
                                    SET NEW.IdEstatus := 1;
                                END IF;
                                
                                SELECT rut, nombres, apellidos
                                INTO lstrDataRut, lstrDataNombres, lstrDataApellidos
                                FROM tbl_personas
                                WHERE tbl_personas.IdPersona = NEW.IdPersona;
                                
                                SET NEW.data_rut = lstrDataRut;
                                SET NEW.data_nombres = lstrDataNombres;
                                SET NEW.data_apellidos = lstrDataApellidos;
                                
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

        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_accesos_BEFORE_INSERT`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `tbl_accesos_BEFORE_INSERT` BEFORE INSERT ON `tbl_accesos` FOR EACH ROW begin
                        DECLARE lintIdEstatus INT;
                            if new.IdTipoAcceso = 1 THEN
                                SET lintIdEstatus := (SELECT 2
                                                FROM tbl_personas
                                                INNER JOIN tbl_contratos_personas ON tbl_personas.IdPersona = tbl_contratos_personas.IdPersona
                                                INNER JOIN tbl_contrato ON tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id
                                                INNER JOIN tbl_contratistas ON tbl_contrato.IdContratista = tbl_contratistas.IdContratista
                                                WHERE tbl_personas.IdPersona = NEW.IdPersona
                                                AND (EXISTS (SELECT tbl_documentos.IdDocumento
                                                             FROM tbl_documentos
                                                             INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                             WHERE tbl_documentos.Entidad = 1
                                                             AND tbl_documentos.IdEntidad = tbl_contratistas.IdContratista
                                                             AND ( (tbl_documentos.IdEstatus NOT IN (4,5)) OR (tbl_documentos.IdEstatusDocumento NOT IN (1)) ) )
                                                  OR EXISTS (SELECT tbl_documentos.IdDocumento
                                                             FROM tbl_documentos
                                                             INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                             WHERE tbl_documentos.Entidad = 2
                                                             AND tbl_documentos.IdEntidad = tbl_contrato.contrato_id
                                                             AND ( (tbl_documentos.IdEstatus NOT IN (4,5)) OR (tbl_documentos.IdEstatusDocumento NOT IN (1)) ) )
                                                  OR EXISTS (SELECT tbl_documentos.IdDocumento
                                                             FROM tbl_documentos
                                                             INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                             WHERE tbl_documentos.Entidad = 3
                                                             AND tbl_documentos.IdEntidad = tbl_personas.IdPersona
                                                             AND ( (tbl_documentos.IdEstatus NOT IN (4,5)) OR (tbl_documentos.IdEstatusDocumento NOT IN (1)) ) ) ) );
                                IF lintIdEstatus = 2 THEN
                                    SET NEW.IdEstatus := 2;
                                ELSE 
                                    SET NEW.IdEstatus := 1;
                                END IF;
                            end if;
                        END
                       ");
        
    }
}
