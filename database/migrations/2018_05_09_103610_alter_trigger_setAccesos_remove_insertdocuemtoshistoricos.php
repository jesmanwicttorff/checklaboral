<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerSetAccesosRemoveInsertdocuemtoshistoricos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `setAccesos`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `setAccesos` 
                        AFTER UPDATE ON `tbl_documentos` FOR EACH ROW
                        BEGIN
                             DECLARE lintIdEstatus, grupo, condicion INT;
                            IF (NEW.IdEstatus != OLD.IdEstatus) OR (NEW.IdEstatusDocumento != OLD.IdEstatusDocumento) THEN
                                     SET lintIdEstatus := 2;
                                  IF (NEW.Entidad = 1) THEN
                                  
                        
                                            UPDATE tbl_accesos SET IdEstatus = fnComprobarAccesos(tbl_accesos.IdPersona), IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                                            WHERE tbl_accesos.IdPersona IN (
                                            SELECT tbl_contratos_personas.IdPersona 
                                            FROM tbl_contratos_personas
                                            INNER JOIN tbl_contrato ON tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id
                                            WHERE tbl_contrato.IdContratista = NEW.IdEntidad
                                            )
                                            AND tbl_accesos.IdTipoAcceso = 1;
                        
                                                  
                                      UPDATE tbl_accesos_activos
                                      SET tbl_accesos_activos.IdEstatus = lintIdEstatus
                                      WHERE tbl_accesos_activos.IdActivoData IN (SELECT IdActivoData
                                                                                 FROM tbl_activos_data
                                                                                 INNER JOIN tbl_contrato ON tbl_activos_data.contrato_id = tbl_contrato.contrato_id
                                                                                 INNER JOIN tbl_contratistas ON tbl_contrato.IdContratista = tbl_contratistas.IdContratista
                                                                                 INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = NEW.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                                                 WHERE tbl_contratistas.IdContratista = NEW.IdEntidad);
                                  ELSEIF (NEW.Entidad = 2) THEN
                                                       
                                            UPDATE tbl_accesos
                                            SET IdEstatus =  fnComprobarAccesos(tbl_accesos.IdPersona), IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                                            WHERE tbl_accesos.IdPersona IN (
                                            SELECT tbl_contratos_personas.IdPersona 
                                            FROM tbl_contratos_personas
                                            WHERE tbl_contratos_personas.contrato_id = NEW.IdEntidad
                                            )
                                            AND tbl_accesos.IdTipoAcceso = 1;
                                            
                                      
                                      UPDATE tbl_accesos_activos
                                      SET tbl_accesos_activos.IdEstatus = lintIdEstatus
                                      WHERE tbl_accesos_activos.IdActivoData IN (SELECT IdActivoData
                                                                                 FROM tbl_activos_data
                                                                                 INNER JOIN tbl_contrato ON tbl_activos_data.contrato_id = tbl_contrato.contrato_id
                                                                                 INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = NEW.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                                                 WHERE tbl_contrato.contrato_id = NEW.IdEntidad);
                                  ELSEIF (NEW.Entidad = 3) THEN
                                       
                                    UPDATE tbl_accesos
                                    SET IdEstatus = fnComprobarAccesos(tbl_accesos.IdPersona),IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                                    WHERE tbl_accesos.IdPersona = NEW.IdEntidad
                                    AND tbl_accesos.IdTipoAcceso = 1;
                                    
                                  ELSEIF (NEW.Entidad = 9) THEN
                                
                                     UPDATE tbl_accesos set IdEstatus = fnComprobarAccesos(tbl_accesos.IdPersona), IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                                                    where tbl_accesos.IdPersona IN (
                                                    Select tbl_contratos_personas.IdPersona 
                                                    from tbl_contratos_personas
                                                    INNER JOIN tbl_contrato on tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id
                                                    INNER JOIN tbl_contratos_subcontratistas on tbl_contrato.contrato_id=tbl_contratos_subcontratistas.contrato_id
                                                    where tbl_contratos_subcontratistas.IdSubContratista = NEW.IdEntidad)
                                                    and tbl_accesos.IdTipoAcceso = 1;
                                
                                    UPDATE tbl_accesos_activos
                                    SET tbl_accesos_activos.IdEstatus = 2
                                    WHERE tbl_accesos_activos.IdActivoData IN (SELECT IdActivoData
                                                                               FROM tbl_activos_data
                                                                               INNER JOIN tbl_contrato ON tbl_activos_data.contrato_id = tbl_contrato.contrato_id
                                                                               INNER JOIN tbl_contratos_subcontratistas on tbl_contrato.contrato_id=tbl_contratos_subcontratistas.contrato_id
                                                                               INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = NEW.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                                               WHERE tbl_contratos_subcontratistas.IdSubContratista = NEW.IdEntidad);
 
                                            
                                  ELSEIF (NEW.Entidad >= 10) THEN
                                      
                                      UPDATE tbl_accesos_activos
                                      SET tbl_accesos_activos.IdEstatus = lintIdEstatus
                                      WHERE tbl_accesos_activos.IdActivoData IN (SELECT IdActivoData
                                                                                 FROM tbl_activos_data
                                                                                 INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = NEW.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                                                 WHERE tbl_activos_data.IdActivoData = NEW.IdEntidad
                                                                                 AND   tbl_activos_data.IdActivo = NEW.Entidad);
                                  END IF;
                            END IF;
                                              
                                  IF (NEW.IdEstatus = 5) THEN		
                                    IF (NEW.Entidad=1) THEN
                                      SET grupo = (SELECT		IdEstatus
                                                   FROM		tbl_contratistas
                                                    WHERE		tbl_contratistas.IdContratista = NEW.IdEntidad
                                                  );
                                      IF (grupo=3) THEN
                                        SET condicion =  (SELECT 	COUNT(*)
                                                          FROM  	tbl_documentos 
                                                          WHERE 	IdEstatus <> 5
                                                          AND		IdEntidad = NEW.IdEntidad
                                                          AND		Entidad = 1
                                                         );                
                                        IF (condicion = 0) THEN
                                          UPDATE	tb_users
                                          SET		active = 1, group_id = 6
                                          WHERE	id = NEW.entry_by_access;
                                          UPDATE	tbl_contratistas
                                          SET		IdEstatus = 1
                                          WHERE	IdContratista = NEW.IdEntidad;
                                        END IF;
                                      END IF;
                                  END IF;
                                END IF;
                                                
                                IF (NEW.IdEstatus != OLD.IdEstatus) THEN
                                    IF NEW.IdEstatus = 5 THEN
                                        CALL GeneraTraspasoDatos(NEW.IdTipoDocumento, NEW.IdDocumento);
                                    END IF;
                                END IF;
                                                                                         
                                CALL prActualizaAlertasXtipoDoc(NEW.IdDocumento,NEW.IdTipoDocumento,NEW.Entidad,NEW.IdEntidad,NEW.IdEstatus,NEW.Contrato_ID,NEW.createdOn,NEW.updatedOn,NEW.FechaVencimiento); 
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
        DB::unprepared("DROP TRIGGER IF EXISTS `setAccesos`;");
    }
}
