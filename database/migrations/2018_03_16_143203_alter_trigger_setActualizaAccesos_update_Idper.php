<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerSetActualizaAccesosUpdateIdper extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `setActualizaAccesos`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `setActualizaAccesos` 
                        AFTER INSERT ON `tbl_documentos` FOR EACH ROW
                        BEGIN
                            DECLARE lintIdNoHace INT;
                            DECLARE lintIdEstatus INT;
                            
                            IF NEW.IdTipoDocumento IN ('26', '44', '66') AND NEW.Entidad = 1 THEN
                                SET lintIdNoHace := 1;
                            ELSE
                                
                              IF (NEW.Entidad = 1) THEN
                                SET lintIdEstatus := 2;
                                
                                 UPDATE tbl_accesos set IdEstatus = fnComprobarAccesos(tbl_accesos.IdPersona), IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                                                where tbl_accesos.IdPersona IN (
                                                Select tbl_contratos_personas.IdPersona 
                                                from tbl_contratos_personas
                                                inner join tbl_contrato on tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id
                                                where tbl_contrato.IdContratista = NEW.IdEntidad
                                                )
                                                and tbl_accesos.IdTipoAcceso = 1;
                                
                                UPDATE tbl_accesos_activos
                                SET tbl_accesos_activos.IdEstatus = 2
                                WHERE tbl_accesos_activos.IdActivoData IN (SELECT IdActivoData
                                                                           FROM tbl_activos_data
                                                                           INNER JOIN tbl_contrato ON tbl_activos_data.contrato_id = tbl_contrato.contrato_id
                                                                           INNER JOIN tbl_contratistas ON tbl_contrato.IdContratista = tbl_contratistas.IdContratista
                                                                           INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = NEW.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                                           WHERE tbl_contratistas.IdContratista = NEW.IdEntidad);
                              ELSEIF (NEW.Entidad = 2) THEN
                                 UPDATE tbl_accesos
                                  set IdEstatus =  fnComprobarAccesos(tbl_accesos.IdPersona), IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                                  where tbl_accesos.IdPersona IN (
                                   Select tbl_contratos_personas.IdPersona 
                                   from tbl_contratos_personas
                                    where tbl_contratos_personas.contrato_id = NEW.IdEntidad
                                      )
                                   and tbl_accesos.IdTipoAcceso = 1;
                                 
                                UPDATE tbl_accesos_activos
                                SET tbl_accesos_activos.IdEstatus = 2
                                WHERE tbl_accesos_activos.IdActivoData IN (SELECT IdActivoData
                                                                           FROM tbl_activos_data
                                                                           INNER JOIN tbl_contrato ON tbl_activos_data.contrato_id = tbl_contrato.contrato_id
                                                                           INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = NEW.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                                           WHERE tbl_contrato.contrato_id = NEW.IdEntidad);
                              ELSEIF (NEW.Entidad = 3) THEN
                                    UPDATE tbl_accesos
                                    set IdEstatus = fnComprobarAccesos(tbl_accesos.IdPersona), IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                                    where tbl_accesos.IdPersona = NEW.IdEntidad
                                    and tbl_accesos.IdTipoAcceso = 1;
                                
                              ELSEIF (NEW.Entidad >= 10) THEN
                                
                                UPDATE tbl_accesos_activos
                                SET tbl_accesos_activos.IdEstatus = 2
                                WHERE tbl_accesos_activos.IdActivoData IN (SELECT IdActivoData
                                                                           FROM tbl_activos_data
                                                                           INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = NEW.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                                           WHERE tbl_activos_data.IdActivoData = NEW.IdEntidad
                                                                           AND   tbl_activos_data.IdActivo = NEW.Entidad);
                              END IF;
                              call prActualizaAlertasXtipoDoc(NEW.IdDocumento,NEW.IdTipoDocumento,NEW.Entidad,NEW.IdEntidad,NEW.IdEstatus,NEW.Contrato_ID,NEW.createdOn,NEW.updatedOn,NEW.FechaVencimiento); 
                              
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
        DB::unprepared("DROP TRIGGER IF EXISTS `setActualizaAccesos`;");
    }
}
