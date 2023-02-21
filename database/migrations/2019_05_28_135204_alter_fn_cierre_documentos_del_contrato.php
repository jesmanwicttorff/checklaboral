<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFnCierreDocumentosDelContrato extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS `fnCierreLaboral`;");
        DB::unprepared('CREATE DEFINER=`root`@`localhost` FUNCTION `fnCierreLaboral`(pdatPeriodo DATE, pintAccion INT) RETURNS varchar(256) CHARSET latin1
        BEGIN
                        
                                              DECLARE ldatUltimo DATE;
                                              DECLARE lstrResultado VARCHAR(256);
                        
                                              -- si el periodo a ejecutar es el ultimo
                                              SET ldatUltimo = NULL;
                                              SET pintAccion = IFNULL(pintAccion,0);
                        
                                              -- Determinamos el maestro de contratos y los prepara para el proceso
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                                                SELECT Max(Periodo)
                                                INTO ldatUltimo
                                                FROM tbl_contrato_maestro;
                        
                                                IF ldatUltimo IS NULL THEN
                        
                                                    -- primera vez que se ejecuta, insertamos todos los contratos vigentes para mes indicado como periodo
                                                    INSERT INTO tbl_contrato_control_laboral(periodo, contrato_id, cont_numero, created_at, updated_at)
                                                    SELECT pdatPeriodo, tbl_contrato.contrato_id, tbl_contrato.cont_numero, now(), now()
                                                    FROM tbl_contrato
                                                    WHERE tbl_contrato.cont_estado = 1
                                                    AND tbl_contrato.cont_fechaInicio <= pdatPeriodo
                                                    AND tbl_contrato.cont_fechaFin >= pdatPeriodo;
                        
                                                ELSE
                        
                                                    IF ldatUltimo = pdatPeriodo AND pintAccion = 0 THEN
                                                      RETURN "02|Se esta intentado procesar un mes que ya se encuentra generado";
                                                    END IF;
                        
                                                    IF ldatUltimo > pdatPeriodo THEN
                                                      RETURN "03|No se puede procesar un periodo anterior";
                                                    END IF;
                        
                                                    IF ldatUltimo < pdatPeriodo THEN
                        
                                                        IF TIMESTAMPDIFF(MONTH, ldatUltimo, pdatPeriodo) = 1 THEN
                        
                                                            -- copiamos los contratos del mes anterior que no se hayan vencidos
                                                            INSERT INTO tbl_contrato_control_laboral(periodo, contrato_id, cont_numero, created_at, updated_at)
                                                            SELECT pdatPeriodo, tbl_contrato_control_laboral.contrato_id, tbl_contrato_control_laboral.cont_numero, now(), now()
                                                            FROM tbl_contrato_control_laboral
                                                            INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_contrato_control_laboral.contrato_id
                                                            WHERE tbl_contrato.cont_estado = 1
                                                            AND tbl_contrato.cont_fechaInicio <= pdatPeriodo
                                                            AND tbl_contrato.cont_fechaFin >= pdatPeriodo
                                                            AND tbl_contrato_control_laboral.Periodo = ldatUltimo;
                        
                                                        ELSE
                                                            RETURN "04|Existen meses sin procesar";
                                                        END IF;
                        
                                                    END IF;
                        
                                                END IF;
                        
                                              END;
                        
                                              -- Limpiamos las tablas
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                        
                                                DELETE FROM tbl_diferencias_calculo WHERE periodo = pdatPeriodo;
                                                DELETE FROM tbl_diferencias_nc_personas WHERE periodo = pdatPeriodo;
                                                DELETE FROM tbl_diferencias_nc_empresas WHERE periodo = pdatPeriodo;
                                                DELETE FROM tbl_riesgo WHERE periodo = pdatPeriodo;
                        
                                                DELETE FROM tbl_personas_maestro WHERE periodo = pdatPeriodo;
                                                DELETE FROM tbl_contrato_maestro WHERE periodo = pdatPeriodo;
                        
                                                -- No hacemos más nada si solo querían eliminar
                                                IF pintAccion = 2 THEN
                                                    RETURN "01|Se eliminó el registro satisfactoriamente";
                                                END IF;
                
                                              END;
                        
                        
                                              -- Cargamos el maestro de personas
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                        
                                                 -- insertamos las personas que estan activas para el periodo de proceso
                                                 INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, Estatus, FechaEfectiva, created_at, updated_at)
                                                 SELECT DISTINCT pdatPeriodo, tbl_personas.IdPersona, tbl_contratos_personas.contrato_id, tbl_contratos_personas.idcontratista, "Vigente", tbl_contratos_personas.FechaInicioFaena, now(), now()
                                                 FROM tbl_personas
                                                 INNER JOIN tbl_contratos_personas ON tbl_contratos_personas.idpersona = tbl_personas.Idpersona
                                                 INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                                                 WHERE ifnull(tbl_contratos_personas.FechaInicioFaena,tbl_contrato.cont_fechaInicio) <= LAST_DAY(pdatPeriodo)
                                                 AND ifnull(tbl_contrato.cont_estado,1) != 2;
                        
                                                 -- insertamos personas que deban un documento y que haya recibido movimiento en la fecha
                                                 INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, Estatus, FechaEfectiva, created_at, updated_at)
                                                 SELECT DISTINCT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, "Finiquitado", null as FechaEfectiva, now(), now()
                                                 FROM tbl_personas
                                                 INNER JOIN tbl_documentos ON tbl_documentos.entidad = 3 and tbl_documentos.IdEntidad = tbl_personas.IdPersona and tbl_documentos.IdEstatus in (1,2,3)
                                                 INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.ControlCheckLaboral = 1
                                                 INNER JOIN tbl_contrato ON tbl_documentos.contrato_id = tbl_contrato.contrato_id
                                                 WHERE EXISTS (SELECT 1
                                                               FROM tbl_movimiento_personal 
                                                               WHERE tbl_movimiento_personal.IdPersona = tbl_personas.IdPersona
                                                               AND tbl_movimiento_personal.contrato_id = tbl_contrato.contrato_id
                                                               AND tbl_movimiento_personal.FechaEfectiva <= LAST_DAY(pdatPeriodo)
                                                               AND IFNULL(tbl_movimiento_personal.Motivo,0) != 1)
                                                 AND NOT EXISTS (SELECT 1 
                                                                 FROM tbl_personas_maestro 
                                                                 WHERE tbl_personas_maestro.Periodo = pdatPeriodo 
                                                                 AND tbl_personas_maestro.IdPersona = tbl_personas.IdPersona 
                                                                 AND tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id)
                                                 AND ifnull(tbl_contrato.cont_estado,1) != 2;
                
                                                 -- insertamos el resto de las personas que hayan algún movimiento en la fecha
                                                 INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, Estatus, FechaEfectiva, created_at, updated_at)
                                                 SELECT DISTINCT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, "Finiquitado", tbl_movimiento_personal.FechaEfectiva, now(), now()
                                                 FROM tbl_personas
                                                 INNER JOIN tbl_movimiento_personal ON tbl_movimiento_personal.IdPersona = tbl_personas.IdPersona 
                                                 INNER JOIN tbl_contrato ON tbl_movimiento_personal.contrato_id = tbl_contrato.contrato_id
                                                 WHERE tbl_movimiento_personal.createdOn >= pdatPeriodo
                                                 AND tbl_movimiento_personal.FechaEfectiva <= LAST_DAY(pdatPeriodo)
                                                 AND IFNULL(tbl_movimiento_personal.Motivo,0) != 1
                                                 AND NOT EXISTS (SELECT 1 
                                                                 FROM tbl_personas_maestro 
                                                                 WHERE tbl_personas_maestro.Periodo = pdatPeriodo 
                                                                 AND tbl_personas_maestro.IdPersona = tbl_personas.IdPersona 
                                                                 AND tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id)
                                                 AND ifnull(tbl_contrato.cont_estado,1) != 2;
                        
                                              END;
                        
                                              -- Cargamos el maestro de contratos
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                        
                                                 -- insertamos el maestro de contratos
                                                 INSERT INTO tbl_contrato_maestro(periodo, idcontratista, contrato_id, dotacion, trabajadores_con_o, costo_laboral, pasivo_laboral, created_at, updated_at)
                                                 SELECT DISTINCT pdatPeriodo, tbl_contrato.idcontratista, tbl_contrato.contrato_id, tbl_contrato.dotacion, 0, 0, 0, now(), now()
                                                 FROM (SELECT DISTINCT pdatPeriodo as periodo, tbl_contrato.idcontratista, tbl_contrato.contrato_id, tbl_contrato.cont_numero, tbl_contrato.cont_fechaFin, count(tbl_personas_maestro.idpersona) as dotacion
                                                       FROM tbl_contrato
                                                       LEFT JOIN tbl_personas_maestro ON tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id AND tbl_personas_maestro.periodo = pdatPeriodo
                                                       WHERE tbl_contrato.cont_fechaInicio <= LAST_DAY(pdatPeriodo)
                                                       AND IFNULL(tbl_contrato.cont_estado,1) != 2
                                                       GROUP BY tbl_contrato.idcontratista, tbl_contrato.contrato_id, tbl_contrato.cont_numero, tbl_contrato.cont_fechaFin) as tbl_contrato
                                                 WHERE  tbl_contrato.cont_fechaFin >= pdatPeriodo
                                                 OR EXISTS (SELECT 1 FROM tbl_personas_maestro WHERE tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id)
                                                 OR EXISTS (SELECT 1
                                                                      FROM tbl_documentos
                                                                      INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.ControlCheckLaboral = 1
                                                                      WHERE tbl_documentos.Entidad = 2
                                                                      AND tbl_documentos.FechaEmision <= pdatPeriodo
                                                                      AND tbl_documentos.IdEntidad = tbl_contrato.contrato_id
                                                                      AND tbl_documentos.IdEstatus != 5);
                        
                                              END;
                
                                              -- Insertamos las no conformidades automaticamente de no finiquitos
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                                                    INSERT INTO tbl_diferencias_nc_personas (periodo,
                                                                IdDocumento,
                                                                contrato_id,
                                                                IdPersona,
                                                                IdTipoDocumento,
                                                                IdEstatusDocumento,
                                                                IdEstatus,
                                                                Resultado,
                                                                entry_by,
                                                                updated_by,
                                                                created_at,
                                                                updated_at)
                                                    SELECT  pdatPeriodo, 
                                                            tbl_documentos.IdDocumento,
                                                            tbl_documentos.contrato_id,
                                                            tbl_documentos.IdEntidad,
                                                            tbl_documentos.IdTipoDocumento,
                                                            tbl_documentos.IdEstatus,
                                                            tbl_documentos.IdEstatus,
                                                            tbl_documentos.Resultado,
                                                            1 as entry_by,
                                                            1 as updated_by,
                                                            now() as created_at,
                                                            now() as updated_at			 
                                                    from tbl_documentos
                                                    inner join tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento =  tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.IdProceso != 4
                                                    where tbl_documentos.entidad = 3
                                                    and tbl_documentos.FechaEmision <= pdatPeriodo
                                                    and exists (select 1 
                                                                from tbl_personas_maestro 
                                                                where tbl_personas_maestro.periodo = pdatPeriodo 
                                                                and tbl_personas_maestro.Idpersona = tbl_documentos.IdEntidad);
                                              END;
        
                                              -- Insertamos las no conformidades de personas finiquitos
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                                                    INSERT INTO tbl_diferencias_nc_personas (periodo,
                                                                IdDocumento,
                                                                contrato_id,
                                                                IdPersona,
                                                                IdTipoDocumento,
                                                                IdEstatusDocumento,
                                                                IdEstatus,
                                                                Resultado,
                                                                entry_by,
                                                                updated_by,
                                                                created_at,
                                                                updated_at)
                                                                SELECT  pdatPeriodo, 
                                                                        tbl_documentos.IdDocumento,
                                                                        tbl_documentos.contrato_id,
                                                                        tbl_documentos.IdEntidad,
                                                                        tbl_documentos.IdTipoDocumento,
                                                                        tbl_documentos.IdEstatus,
                                                                        tbl_documentos.IdEstatus,
                                                                        tbl_documentos.Resultado,
                                                                        1 as entry_by,
                                                                        1 as updated_by,
                                                                        now() as created_at,
                                                                        now() as updated_at			 
                                                                FROM (
                                                                    SELECT tbl_documentos.IdDocumento,
                                                                        tbl_documentos.contrato_id,
                                                                        tbl_documentos.IdEntidad,
                                                                        tbl_documentos.IdTipoDocumento,
                                                                        tbl_documentos.IdEstatus,
                                                                        tbl_documentos.Resultado,
                                                                        tbl_documento_valor.Valor AS FechaFiniquito
                                                                    FROM tbl_documentos
                                                                    INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.IdProceso = 4
                                                                    LEFT JOIN tbl_documento_valor ON tbl_documento_valor.IdDocumento = tbl_documentos.IdDocumento AND tbl_documento_valor.IdTipoDocumentoValor = 171 
                                                                    WHERE tbl_documentos.entidad = 3
                                                                    UNION ALL 
                                                                    SELECT 
                                                                        tbl_documentos_rep_historico.IdDocumento,
                                                                        tbl_documentos_rep_historico.contrato_id,
                                                                        tbl_documentos_rep_historico.IdEntidad,
                                                                        tbl_documentos_rep_historico.IdTipoDocumento,
                                                                        tbl_documentos_rep_historico.IdEstatus,
                                                                        tbl_documentos_rep_historico.Resultado,
                                                                        IFNULL(tbl_documento_valor_historico.Valor, tbl_documento_valor.Valor) AS FechaFiniquito
                                                                    FROM tbl_documentos_rep_historico
                                                                    INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos_rep_historico.IdTipoDocumento AND tbl_tipos_documentos.IdProceso = 4
                                                                    LEFT JOIN tbl_documento_valor_historico ON tbl_documento_valor_historico.IdDocumento = tbl_documentos_rep_historico.IdDocumento AND tbl_documento_valor_historico.IdTipoDocumentoValor = 171
                                                                    LEFT JOIN tbl_documento_valor ON tbl_documento_valor.IdDocumento = tbl_documentos_rep_historico.IdDocumento AND tbl_documento_valor.IdTipoDocumentoValor = 171
                                                                    WHERE tbl_documentos_rep_historico.entidad = 3) as tbl_documentos
                                                                where tbl_documentos.FechaFiniquito <= LAST_DAY(pdatPeriodo)
                                                                and exists (select 1 
                                                                            from tbl_personas_maestro 
                                                                            where tbl_personas_maestro.periodo = pdatPeriodo
                                                                            and tbl_personas_maestro.Idpersona = tbl_documentos.IdEntidad);
                                                                            
                                              END;
                
                                            -- Insertamos las no conformidades automaticamente de empresas
                                            -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                            BEGIN
                                                    
                                                INSERT INTO tbl_diferencias_nc_empresas (periodo,
                                                                IdDocumento,
                                                                IdContratista,
                                                                contrato_id,
                                                                IdTipoDocumento,
                                                                IdEstatusDocumento,
                                                                IdEstatus,
                                                                Resultado,
                                                                entry_by,
                                                                updated_by,
                                                                created_at,
                                                                updated_at)
                                                SELECT  pdatPeriodo, 
                                                        IdDocumento,
                                                        IdContratista,
                                                        contrato_id,
                                                        IdTipoDocumento,
                                                        IdEstatus,
                                                        IdEstatus,
                                                        Resultado,
                                                        1 as entry_by,
                                                        1 as updated_by,
                                                        now() as created_at,
                                                        now() as updated_at			 
                                                FROM tbl_documentos
                                                WHERE entidad in (1, 2, 6)
                                                AND FechaEmision <= pdatPeriodo
                                                AND EXISTS (SELECT 1 
                                                            FROM tbl_contrato_maestro 
                                                            WHERE tbl_contrato_maestro.periodo = pdatPeriodo
                                                            AND (tbl_contrato_maestro.contrato_id = tbl_documentos.contrato_id OR tbl_contrato_maestro.idcontratista = tbl_documentos.IdContratista) );
                                                                        
                                              END;
                        
                                              RETURN "01|Proceso de cierre finalizado satisfactoriamente";
                        
                                            END');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS `fnCierreLaboral`;");
        DB::unprepared('CREATE DEFINER=`root`@`localhost` FUNCTION `fnCierreLaboral`(pdatPeriodo DATE, pintAccion INT) RETURNS varchar(256) CHARSET latin1
        BEGIN
                        
                                              DECLARE ldatUltimo DATE;
                                              DECLARE lstrResultado VARCHAR(256);
                        
                                              -- si el periodo a ejecutar es el ultimo
                                              SET ldatUltimo = NULL;
                                              SET pintAccion = IFNULL(pintAccion,0);
                        
                                              -- Determinamos el maestro de contratos y los prepara para el proceso
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                                                SELECT Max(Periodo)
                                                INTO ldatUltimo
                                                FROM tbl_contrato_maestro;
                        
                                                IF ldatUltimo IS NULL THEN
                        
                                                    -- primera vez que se ejecuta, insertamos todos los contratos vigentes para mes indicado como periodo
                                                    INSERT INTO tbl_contrato_control_laboral(periodo, contrato_id, cont_numero, created_at, updated_at)
                                                    SELECT pdatPeriodo, tbl_contrato.contrato_id, tbl_contrato.cont_numero, now(), now()
                                                    FROM tbl_contrato
                                                    WHERE tbl_contrato.cont_estado = 1
                                                    AND tbl_contrato.cont_fechaInicio <= pdatPeriodo
                                                    AND tbl_contrato.cont_fechaFin >= pdatPeriodo;
                        
                                                ELSE
                        
                                                    IF ldatUltimo = pdatPeriodo AND pintAccion = 0 THEN
                                                      RETURN "02|Se esta intentado procesar un mes que ya se encuentra generado";
                                                    END IF;
                        
                                                    IF ldatUltimo > pdatPeriodo THEN
                                                      RETURN "03|No se puede procesar un periodo anterior";
                                                    END IF;
                        
                                                    IF ldatUltimo < pdatPeriodo THEN
                        
                                                        IF TIMESTAMPDIFF(MONTH, ldatUltimo, pdatPeriodo) = 1 THEN
                        
                                                            -- copiamos los contratos del mes anterior que no se hayan vencidos
                                                            INSERT INTO tbl_contrato_control_laboral(periodo, contrato_id, cont_numero, created_at, updated_at)
                                                            SELECT pdatPeriodo, tbl_contrato_control_laboral.contrato_id, tbl_contrato_control_laboral.cont_numero, now(), now()
                                                            FROM tbl_contrato_control_laboral
                                                            INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_contrato_control_laboral.contrato_id
                                                            WHERE tbl_contrato.cont_estado = 1
                                                            AND tbl_contrato.cont_fechaInicio <= pdatPeriodo
                                                            AND tbl_contrato.cont_fechaFin >= pdatPeriodo
                                                            AND tbl_contrato_control_laboral.Periodo = ldatUltimo;
                        
                                                        ELSE
                                                            RETURN "04|Existen meses sin procesar";
                                                        END IF;
                        
                                                    END IF;
                        
                                                END IF;
                        
                                              END;
                        
                                              -- Limpiamos las tablas
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                        
                                                DELETE FROM tbl_diferencias_calculo WHERE periodo = pdatPeriodo;
                                                DELETE FROM tbl_diferencias_nc_personas WHERE periodo = pdatPeriodo;
                                                DELETE FROM tbl_diferencias_nc_empresas WHERE periodo = pdatPeriodo;
                                                DELETE FROM tbl_riesgo WHERE periodo = pdatPeriodo;
                        
                                                DELETE FROM tbl_personas_maestro WHERE periodo = pdatPeriodo;
                                                DELETE FROM tbl_contrato_maestro WHERE periodo = pdatPeriodo;
                        
                                                -- No hacemos más nada si solo querían eliminar
                                                IF pintAccion = 2 THEN
                                                    RETURN "01|Se eliminó el registro satisfactoriamente";
                                                END IF;
                
                                              END;
                        
                        
                                              -- Cargamos el maestro de personas
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                        
                                                 -- insertamos las personas que estan activas para el periodo de proceso
                                                 INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, Estatus, FechaEfectiva, created_at, updated_at)
                                                 SELECT DISTINCT pdatPeriodo, tbl_personas.IdPersona, tbl_contratos_personas.contrato_id, tbl_contratos_personas.idcontratista, "Vigente", tbl_contratos_personas.FechaInicioFaena, now(), now()
                                                 FROM tbl_personas
                                                 INNER JOIN tbl_contratos_personas ON tbl_contratos_personas.idpersona = tbl_personas.Idpersona
                                                 INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                                                 WHERE ifnull(tbl_contratos_personas.FechaInicioFaena,tbl_contrato.cont_fechaInicio) <= LAST_DAY(pdatPeriodo)
                                                 AND ifnull(tbl_contrato.cont_estado,1) != 2;
                        
                                                 -- insertamos personas que deban un documento y que haya recibido movimiento en la fecha
                                                 INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, Estatus, FechaEfectiva, created_at, updated_at)
                                                 SELECT DISTINCT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, "Finiquitado", null as FechaEfectiva, now(), now()
                                                 FROM tbl_personas
                                                 INNER JOIN tbl_documentos ON tbl_documentos.entidad = 3 and tbl_documentos.IdEntidad = tbl_personas.IdPersona and tbl_documentos.IdEstatus in (1,2,3)
                                                 INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.ControlCheckLaboral = 1
                                                 INNER JOIN tbl_contrato ON tbl_documentos.contrato_id = tbl_contrato.contrato_id
                                                 WHERE EXISTS (SELECT 1
                                                               FROM tbl_movimiento_personal 
                                                               WHERE tbl_movimiento_personal.IdPersona = tbl_personas.IdPersona 
                                                               AND tbl_movimiento_personal.FechaEfectiva <= LAST_DAY(pdatPeriodo)
                                                               AND IFNULL(tbl_movimiento_personal.Motivo,0) != 1)
                                                 AND NOT EXISTS (SELECT 1 
                                                                 FROM tbl_personas_maestro 
                                                                 WHERE tbl_personas_maestro.Periodo = pdatPeriodo 
                                                                 AND tbl_personas_maestro.IdPersona = tbl_personas.IdPersona 
                                                                 AND tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id)
                                                 AND ifnull(tbl_contrato.cont_estado,1) != 2;
                
                                                 -- insertamos el resto de las personas que hayan algún movimiento en la fecha
                                                 INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, Estatus, FechaEfectiva, created_at, updated_at)
                                                 SELECT DISTINCT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, "Finiquitado", tbl_movimiento_personal.FechaEfectiva, now(), now()
                                                 FROM tbl_personas
                                                 INNER JOIN tbl_movimiento_personal ON tbl_movimiento_personal.IdPersona = tbl_personas.IdPersona 
                                                 INNER JOIN tbl_contrato ON tbl_movimiento_personal.contrato_id = tbl_contrato.contrato_id
                                                 WHERE tbl_movimiento_personal.createdOn >= pdatPeriodo
                                                 AND tbl_movimiento_personal.FechaEfectiva <= LAST_DAY(pdatPeriodo)
                                                 AND IFNULL(tbl_movimiento_personal.Motivo,0) != 1
                                                 AND NOT EXISTS (SELECT 1 
                                                                 FROM tbl_personas_maestro 
                                                                 WHERE tbl_personas_maestro.Periodo = pdatPeriodo 
                                                                 AND tbl_personas_maestro.IdPersona = tbl_personas.IdPersona 
                                                                 AND tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id)
                                                 AND ifnull(tbl_contrato.cont_estado,1) != 2;
                        
                                              END;
                        
                                              -- Cargamos el maestro de contratos
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                        
                                                 -- insertamos el maestro de contratos
                                                 INSERT INTO tbl_contrato_maestro(periodo, idcontratista, contrato_id, dotacion, trabajadores_con_o, costo_laboral, pasivo_laboral, created_at, updated_at)
                                                 SELECT DISTINCT pdatPeriodo, tbl_contrato.idcontratista, tbl_contrato.contrato_id, tbl_contrato.dotacion, 0, 0, 0, now(), now()
                                                 FROM (SELECT DISTINCT pdatPeriodo as periodo, tbl_contrato.idcontratista, tbl_contrato.contrato_id, tbl_contrato.cont_numero, tbl_contrato.cont_fechaFin, count(tbl_personas_maestro.idpersona) as dotacion
                                                       FROM tbl_contrato
                                                       LEFT JOIN tbl_personas_maestro ON tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id AND tbl_personas_maestro.periodo = pdatPeriodo
                                                       WHERE tbl_contrato.cont_fechaInicio <= LAST_DAY(pdatPeriodo)
                                                       AND IFNULL(tbl_contrato.cont_estado,1) != 2
                                                       GROUP BY tbl_contrato.idcontratista, tbl_contrato.contrato_id, tbl_contrato.cont_numero, tbl_contrato.cont_fechaFin) as tbl_contrato
                                                 WHERE  tbl_contrato.cont_fechaFin >= pdatPeriodo
                                                 OR EXISTS (SELECT 1 FROM tbl_personas_maestro WHERE tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id)
                                                 OR EXISTS (SELECT 1
                                                                      FROM tbl_documentos
                                                                      INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.ControlCheckLaboral = 1
                                                                      WHERE tbl_documentos.Entidad = 2
                                                                      AND tbl_documentos.FechaEmision <= pdatPeriodo
                                                                      AND tbl_documentos.IdEntidad = tbl_contrato.contrato_id
                                                                      AND tbl_documentos.IdEstatus != 5);
                        
                                              END;
                
                                              -- Insertamos las no conformidades automaticamente de no finiquitos
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                                                    INSERT INTO tbl_diferencias_nc_personas (periodo,
                                                                IdDocumento,
                                                                contrato_id,
                                                                IdPersona,
                                                                IdTipoDocumento,
                                                                IdEstatusDocumento,
                                                                IdEstatus,
                                                                Resultado,
                                                                entry_by,
                                                                updated_by,
                                                                created_at,
                                                                updated_at)
                                                    SELECT  pdatPeriodo, 
                                                            tbl_documentos.IdDocumento,
                                                            tbl_documentos.contrato_id,
                                                            tbl_documentos.IdEntidad,
                                                            tbl_documentos.IdTipoDocumento,
                                                            tbl_documentos.IdEstatus,
                                                            tbl_documentos.IdEstatus,
                                                            tbl_documentos.Resultado,
                                                            1 as entry_by,
                                                            1 as updated_by,
                                                            now() as created_at,
                                                            now() as updated_at			 
                                                    from tbl_documentos
                                                    inner join tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento =  tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.IdProceso != 4
                                                    where tbl_documentos.entidad = 3
                                                    and tbl_documentos.FechaEmision <= pdatPeriodo
                                                    and exists (select 1 
                                                                from tbl_personas_maestro 
                                                                where tbl_personas_maestro.periodo = pdatPeriodo 
                                                                and tbl_personas_maestro.Idpersona = tbl_documentos.IdEntidad);
                                              END;
        
                                              -- Insertamos las no conformidades de personas finiquitos
                                              -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                              BEGIN
                                                    INSERT INTO tbl_diferencias_nc_personas (periodo,
                                                                IdDocumento,
                                                                contrato_id,
                                                                IdPersona,
                                                                IdTipoDocumento,
                                                                IdEstatusDocumento,
                                                                IdEstatus,
                                                                Resultado,
                                                                entry_by,
                                                                updated_by,
                                                                created_at,
                                                                updated_at)
                                                                SELECT  pdatPeriodo, 
                                                                        tbl_documentos.IdDocumento,
                                                                        tbl_documentos.contrato_id,
                                                                        tbl_documentos.IdEntidad,
                                                                        tbl_documentos.IdTipoDocumento,
                                                                        tbl_documentos.IdEstatus,
                                                                        tbl_documentos.IdEstatus,
                                                                        tbl_documentos.Resultado,
                                                                        1 as entry_by,
                                                                        1 as updated_by,
                                                                        now() as created_at,
                                                                        now() as updated_at			 
                                                                FROM (
                                                                    SELECT tbl_documentos.IdDocumento,
                                                                        tbl_documentos.contrato_id,
                                                                        tbl_documentos.IdEntidad,
                                                                        tbl_documentos.IdTipoDocumento,
                                                                        tbl_documentos.IdEstatus,
                                                                        tbl_documentos.Resultado,
                                                                        tbl_documento_valor.Valor AS FechaFiniquito
                                                                    FROM tbl_documentos
                                                                    INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.IdProceso = 4
                                                                    LEFT JOIN tbl_documento_valor ON tbl_documento_valor.IdDocumento = tbl_documentos.IdDocumento AND tbl_documento_valor.IdTipoDocumentoValor = 171 
                                                                    WHERE tbl_documentos.entidad = 3
                                                                    UNION ALL 
                                                                    SELECT 
                                                                        tbl_documentos_rep_historico.IdDocumento,
                                                                        tbl_documentos_rep_historico.contrato_id,
                                                                        tbl_documentos_rep_historico.IdEntidad,
                                                                        tbl_documentos_rep_historico.IdTipoDocumento,
                                                                        tbl_documentos_rep_historico.IdEstatus,
                                                                        tbl_documentos_rep_historico.Resultado,
                                                                        IFNULL(tbl_documento_valor_historico.Valor, tbl_documento_valor.Valor) AS FechaFiniquito
                                                                    FROM tbl_documentos_rep_historico
                                                                    INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos_rep_historico.IdTipoDocumento AND tbl_tipos_documentos.IdProceso = 4
                                                                    LEFT JOIN tbl_documento_valor_historico ON tbl_documento_valor_historico.IdDocumento = tbl_documentos_rep_historico.IdDocumento AND tbl_documento_valor_historico.IdTipoDocumentoValor = 171
                                                                    LEFT JOIN tbl_documento_valor ON tbl_documento_valor.IdDocumento = tbl_documentos_rep_historico.IdDocumento AND tbl_documento_valor.IdTipoDocumentoValor = 171
                                                                    WHERE tbl_documentos_rep_historico.entidad = 3) as tbl_documentos
                                                                where tbl_documentos.FechaFiniquito <= LAST_DAY(pdatPeriodo)
                                                                and exists (select 1 
                                                                            from tbl_personas_maestro 
                                                                            where tbl_personas_maestro.periodo = pdatPeriodo
                                                                            and tbl_personas_maestro.Idpersona = tbl_documentos.IdEntidad);
                                                                            
                                              END;
                
                                            -- Insertamos las no conformidades automaticamente de empresas
                                            -- ----------------------------------------------------------------------------------------------------------------------------------------------
                                            BEGIN
                                                    
                                                INSERT INTO tbl_diferencias_nc_empresas (periodo,
                                                                IdDocumento,
                                                                IdContratista,
                                                                contrato_id,
                                                                IdTipoDocumento,
                                                                IdEstatusDocumento,
                                                                IdEstatus,
                                                                Resultado,
                                                                entry_by,
                                                                updated_by,
                                                                created_at,
                                                                updated_at)
                                                SELECT  pdatPeriodo, 
                                                        IdDocumento,
                                                        IdContratista,
                                                        contrato_id,
                                                        IdTipoDocumento,
                                                        IdEstatus,
                                                        IdEstatus,
                                                        Resultado,
                                                        1 as entry_by,
                                                        1 as updated_by,
                                                        now() as created_at,
                                                        now() as updated_at			 
                                                FROM tbl_documentos
                                                WHERE entidad in (1, 2, 6)
                                                AND FechaEmision <= pdatPeriodo
                                                AND EXISTS (SELECT 1 
                                                            FROM tbl_contrato_maestro 
                                                            WHERE tbl_contrato_maestro.periodo = pdatPeriodo
                                                            AND (tbl_contrato_maestro.contrato_id = tbl_documentos.contrato_id OR tbl_contrato_maestro.idcontratista = tbl_documentos.IdContratista) );
                                                                        
                                              END;
                        
                                              RETURN "01|Proceso de cierre finalizado satisfactoriamente";
                        
                                            END');
    }
}
