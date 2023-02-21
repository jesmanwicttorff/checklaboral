<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFnComprobarAccesosDocumentosContratoActivo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS `fnComprobarAccesos`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` FUNCTION `fnComprobarAccesos`(lintIdPersona int) RETURNS int(11)
        BEGIN
                          DECLARE lintExiste INT;
                          DECLARE lintContadorp INT;
                          DECLARE lintContadorcatr INT;
                          DECLARE lintContadorcontt INT;
                          DECLARE lintContadorsubcontt INT;
                          DECLARE lintHabilitado INT;
                          
                          SET lintExiste := 0;
                          SET lintContadorp := 0;
                          SET lintContadorcatr := 0;
                          SET lintContadorcontt := 0;
                          SET lintContadorsubcontt := 0;
                          SET lintHabilitado := 1;
                          
                          SELECT COUNT(*)
                          INTO lintExiste 
                          FROM tbl_personas
                          WHERE idpersona = lintIdPersona;
                          
                          IF lintExiste > 0 THEN 
                          
                            
                                SELECT COUNT(tbl_documentos.IdDocumento)
                                INTO lintContadorp
                                FROM tbl_documentos
                                INNER JOIN tbl_contratos_personas ON tbl_documentos.IdEntidad = tbl_contratos_personas.IdPersona AND tbl_documentos.contrato_id = tbl_contratos_personas.contrato_id
                                INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                where tbl_documentos.Entidad = 3 
                                and tbl_documentos.IdEntidad=lintIdPersona
                                and ( tbl_documentos.IdEstatus NOT IN (4,5) or ifnull(tbl_documentos.IdEstatusDocumento,1) != 1 )
                                and ( NOT EXISTS(
                                                select doc.IdDocumento 
                                                        from tbl_documentos as doc 
                                                        where doc.IdDocumento =  tbl_documentos.IdDocumentoRelacion
                                                        and doc.IdEstatus IN (4,5) 
                                                        and ifnull(doc.IdEstatusDocumento,1) = 1 
                                                )
                                );
                                
                                IF lintContadorp > 0 THEN
                                    SET lintHabilitado = 2;
                                ELSE
                                     SELECT COUNT(tbl_documentos.IdDocumento)
                                     INTO lintContadorcatr
                                         FROM tbl_documentos
                                        INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                        INNER JOIN tbl_contrato ON  tbl_documentos.IdEntidad = tbl_contrato.contrato_id
                                        INNER JOIN tbl_contratos_personas ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                                        where tbl_documentos.Entidad = 2
                                        and tbl_contratos_personas.IdPersona=lintIdPersona
                                        and ( tbl_documentos.IdEstatus NOT IN (4,5) or ifnull(tbl_documentos.IdEstatusDocumento,1) != 1 )
                                        and ( NOT EXISTS(
                                                    select doc.IdDocumento 
                                                            from tbl_documentos as doc 
                                                            where doc.IdDocumento =  tbl_documentos.IdDocumentoRelacion
                                                            and doc.IdEstatus IN (4,5) 
                                                            and ifnull(doc.IdEstatusDocumento,1) = 1
                                                    )
                                        );
                                    
                                    IF lintContadorcatr > 0 THEN
                                         SET lintHabilitado = 2;
                                    ELSE
                                        SELECT COUNT(tbl_documentos.IdDocumento)
                                        INTO lintContadorcontt
                                         FROM tbl_documentos
                                        INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                        INNER JOIN tbl_contratistas ON tbl_documentos.IdEntidad = tbl_contratistas.IdContratista
                                        INNER JOIN tbl_contrato ON  tbl_contratistas.IdContratista = tbl_contrato.IdContratista
                                        INNER JOIN tbl_contratos_personas ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                                        where tbl_documentos.Entidad = 1
                                        and tbl_contratos_personas.IdPersona=lintIdPersona
                                        and ( tbl_documentos.IdEstatus NOT IN (4,5) or ifnull(tbl_documentos.IdEstatusDocumento,1) != 1 )
                                        and ( NOT EXISTS(
                                                    select doc.IdDocumento 
                                                            from tbl_documentos as doc 
                                                            where doc.IdDocumento =  tbl_documentos.IdDocumentoRelacion
                                                            and doc.IdEstatus IN (4,5) 
                                                            and ifnull(doc.IdEstatusDocumento,1) = 1
                                                    )
                                        );
                                        IF lintContadorcontt > 0 THEN
                                             SET lintHabilitado = 2;
                                        ELSE
                                            
                                            SELECT COUNT(tbl_documentos.IdDocumento)
                                            INTO lintContadorsubcontt
                                            FROM tbl_documentos
                                            INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                            INNER JOIN tbl_contratos_personas ON tbl_contratos_personas.contrato_id = tbl_documentos.contrato_id AND tbl_contratos_personas.IdContratista = tbl_documentos.IdEntidad
                                            WHERE tbl_contratos_personas.IdPersona = lintIdPersona
                                            AND tbl_documentos.entidad = 9
                                            AND ( tbl_documentos.IdEstatus NOT IN (4,5) or ifnull(tbl_documentos.IdEstatusDocumento,1) != 1 )
                                            AND ( NOT EXISTS(
                                                        SELECT doc.IdDocumento
                                                        FROM tbl_documentos as doc 
                                                        WHERE doc.IdDocumento =  tbl_documentos.IdDocumentoRelacion
                                                        AND doc.IdEstatus IN (4,5) 
                                                        AND ifnull(doc.IdEstatusDocumento,1) = 1
                                                    )
                                            );
                                            
                                        IF lintContadorsubcontt > 0 THEN
                                             SET lintHabilitado = 2;
                                        ELSE
                                             SET lintHabilitado = 1;
                                        END IF;     
                                     
                                       END IF;
                                    
                                    END IF;
                                    
                                END IF;
                          ELSE 
                              SET lintHabilitado = 0;
                          END IF;
                     RETURN lintHabilitado;     
                END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS `fnComprobarAccesos`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` FUNCTION `fnComprobarAccesos`(lintIdPersona int) RETURNS int(11)
        BEGIN
                          DECLARE lintExiste INT;
                          DECLARE lintContadorp INT;
                          DECLARE lintContadorcatr INT;
                          DECLARE lintContadorcontt INT;
                          DECLARE lintContadorsubcontt INT;
                          DECLARE lintHabilitado INT;
                          
                          SET lintExiste := 0;
                          SET lintContadorp := 0;
                          SET lintContadorcatr := 0;
                          SET lintContadorcontt := 0;
                          SET lintContadorsubcontt := 0;
                          SET lintHabilitado := 1;
                          
                          SELECT COUNT(*)
                          INTO lintExiste 
                          FROM tbl_personas
                          WHERE idpersona = lintIdPersona;
                          
                          IF lintExiste > 0 THEN 
                          
                            
                               SELECT COUNT(tbl_documentos.IdDocumento)
                               INTO lintContadorp
                                     FROM tbl_documentos
                                    INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                    where tbl_documentos.Entidad = 3 
                                    and tbl_documentos.IdEntidad=lintIdPersona
                                    and ( tbl_documentos.IdEstatus NOT IN (4,5) or ifnull(tbl_documentos.IdEstatusDocumento,1) != 1 )
                                    and ( NOT EXISTS(
                                                    select doc.IdDocumento 
                                                            from tbl_documentos as doc 
                                                            where doc.IdDocumento =  tbl_documentos.IdDocumentoRelacion
                                                            and doc.IdEstatus IN (4,5) 
                                                            and ifnull(doc.IdEstatusDocumento,1) = 1 
                                                    )
                                    );
                                
                                IF lintContadorp > 0 THEN
                                    SET lintHabilitado = 2;
                                ELSE
                                     SELECT COUNT(tbl_documentos.IdDocumento)
                                     INTO lintContadorcatr
                                         FROM tbl_documentos
                                        INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                        INNER JOIN tbl_contrato ON  tbl_documentos.IdEntidad = tbl_contrato.contrato_id
                                        INNER JOIN tbl_contratos_personas ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                                        where tbl_documentos.Entidad = 2
                                        and tbl_contratos_personas.IdPersona=lintIdPersona
                                        and ( tbl_documentos.IdEstatus NOT IN (4,5) or ifnull(tbl_documentos.IdEstatusDocumento,1) != 1 )
                                        and ( NOT EXISTS(
                                                    select doc.IdDocumento 
                                                            from tbl_documentos as doc 
                                                            where doc.IdDocumento =  tbl_documentos.IdDocumentoRelacion
                                                            and doc.IdEstatus IN (4,5) 
                                                            and ifnull(doc.IdEstatusDocumento,1) = 1
                                                    )
                                        );
                                    
                                    IF lintContadorcatr > 0 THEN
                                         SET lintHabilitado = 2;
                                    ELSE
                                        SELECT COUNT(tbl_documentos.IdDocumento)
                                        INTO lintContadorcontt
                                         FROM tbl_documentos
                                        INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                        INNER JOIN tbl_contratistas ON tbl_documentos.IdEntidad = tbl_contratistas.IdContratista
                                        INNER JOIN tbl_contrato ON  tbl_contratistas.IdContratista = tbl_contrato.IdContratista
                                        INNER JOIN tbl_contratos_personas ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                                        where tbl_documentos.Entidad = 1
                                        and tbl_contratos_personas.IdPersona=lintIdPersona
                                        and ( tbl_documentos.IdEstatus NOT IN (4,5) or ifnull(tbl_documentos.IdEstatusDocumento,1) != 1 )
                                        and ( NOT EXISTS(
                                                    select doc.IdDocumento 
                                                            from tbl_documentos as doc 
                                                            where doc.IdDocumento =  tbl_documentos.IdDocumentoRelacion
                                                            and doc.IdEstatus IN (4,5) 
                                                            and ifnull(doc.IdEstatusDocumento,1) = 1
                                                    )
                                        );
                                        IF lintContadorcontt > 0 THEN
                                             SET lintHabilitado = 2;
                                        ELSE
                                            
                                            SELECT COUNT(tbl_documentos.IdDocumento)
                                            INTO lintContadorsubcontt
                                            FROM tbl_documentos
                                            INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.BloqueaAcceso = 'SI'
                                            INNER JOIN tbl_contratos_personas ON tbl_contratos_personas.contrato_id = tbl_documentos.contrato_id AND tbl_contratos_personas.IdContratista = tbl_documentos.IdEntidad
                                            WHERE tbl_contratos_personas.IdPersona = lintIdPersona
                                            AND tbl_documentos.entidad = 9
                                            AND ( tbl_documentos.IdEstatus NOT IN (4,5) or ifnull(tbl_documentos.IdEstatusDocumento,1) != 1 )
                                            AND ( NOT EXISTS(
                                                        SELECT doc.IdDocumento
                                                        FROM tbl_documentos as doc 
                                                        WHERE doc.IdDocumento =  tbl_documentos.IdDocumentoRelacion
                                                        AND doc.IdEstatus IN (4,5) 
                                                        AND ifnull(doc.IdEstatusDocumento,1) = 1
                                                    )
                                            );
                                            
                                        IF lintContadorsubcontt > 0 THEN
                                             SET lintHabilitado = 2;
                                        ELSE
                                             SET lintHabilitado = 1;
                                        END IF;     
                                     
                                       END IF;
                                    
                                    END IF;
                                    
                                END IF;
                          ELSE 
                              SET lintHabilitado = 0;
                          END IF;
                     RETURN lintHabilitado;     
                END");
    }
}
