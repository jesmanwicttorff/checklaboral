<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerTblDocumentosAfterDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_documentos_AFTER_DELETE`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `tbl_documentos_AFTER_DELETE` 
                        AFTER DELETE ON `tbl_documentos` FOR EACH ROW
                        BEGIN
                            DECLARE lintIdEstatus INT;

	                        IF (OLD.Entidad = 1) THEN
	      
                                UPDATE tbl_accesos set IdEstatus = fnComprobarAccesos(tbl_accesos.IdPersona), IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                                where tbl_accesos.IdPersona IN (
                                Select tbl_contratos_personas.IdPersona 
                                from tbl_contratos_personas
                                inner join tbl_contrato on tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id
                                where tbl_contrato.IdContratista = OLD.IdEntidad
                                )
                                and tbl_accesos.IdTipoAcceso = 1;
            
                                      
                              UPDATE tbl_accesos_activos
                              SET tbl_accesos_activos.IdEstatus = lintIdEstatus
                              WHERE tbl_accesos_activos.IdActivoData IN (SELECT IdActivoData
                                                                         FROM tbl_activos_data
                                                                         INNER JOIN tbl_contrato ON tbl_activos_data.contrato_id = tbl_contrato.contrato_id
                                                                         INNER JOIN tbl_contratistas ON tbl_contrato.IdContratista = tbl_contratistas.IdContratista
                                                                         INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = OLD.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                                         WHERE tbl_contratistas.IdContratista = OLD.IdEntidad);
                        ELSEIF (OLD.Entidad = 2) THEN
                               
                            UPDATE tbl_accesos
                            set IdEstatus =  fnComprobarAccesos(tbl_accesos.IdPersona), IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                            where tbl_accesos.IdPersona IN (
                            Select tbl_contratos_personas.IdPersona 
                            from tbl_contratos_personas
                            where tbl_contratos_personas.contrato_id = OLD.IdEntidad
                            )
                            and tbl_accesos.IdTipoAcceso = 1;
                    
              
                          UPDATE tbl_accesos_activos
                          SET tbl_accesos_activos.IdEstatus = lintIdEstatus
                          WHERE tbl_accesos_activos.IdActivoData IN (SELECT IdActivoData
                                                                     FROM tbl_activos_data
                                                                     INNER JOIN tbl_contrato ON tbl_activos_data.contrato_id = tbl_contrato.contrato_id
                                                                     INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = OLD.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                                                     WHERE tbl_contrato.contrato_id = OLD.IdEntidad);
	                    ELSEIF (OLD.Entidad = 3) THEN
	           
                            UPDATE tbl_accesos
                            set IdEstatus = fnComprobarAccesos(tbl_accesos.IdPersona),IdEstatusUsuario = fnComprobarAccesos(tbl_accesos.IdPersona)
                            where tbl_accesos.IdPersona = OLD.IdEntidad
                            and tbl_accesos.IdTipoAcceso = 1;
                    
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
    }
}
