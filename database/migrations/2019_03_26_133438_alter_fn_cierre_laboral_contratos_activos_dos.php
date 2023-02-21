<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFnCierreLaboralContratosActivosDos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::unprepared("DROP FUNCTION IF EXISTS `fnCierreLaboral`;");
      DB::unprepared('CREATE DEFINER=`root`@`localhost` FUNCTION `fnCierreLaboral`(pdatPeriodo DATE, pbolSobreescribir INT) RETURNS varchar(256) CHARSET latin1
BEGIN

                      DECLARE ldatUltimo DATE;
                      DECLARE lstrResultado VARCHAR(256);

                      -- si el periodo a ejecutar es el ultimo
                      SET ldatUltimo = NULL;
                      SET pbolSobreescribir = IFNULL(pbolSobreescribir,0);

                      -- Determinamos el maestro de contratos y los prepara para el proceso
                      -- ----------------------------------------------------------------------------------------------------------------------------------------------
                      BEGIN
                        SELECT Max(Periodo)
                        INTO ldatUltimo
                        FROM tbl_contrato_control_laboral;

                        IF ldatUltimo IS NULL THEN

                            -- primera vez que se ejecuta, insertamos todos los contratos vigentes para mes indicado como periodo
                            INSERT INTO tbl_contrato_control_laboral(periodo, contrato_id, cont_numero, created_at, updated_at)
                            SELECT pdatPeriodo, tbl_contrato.contrato_id, tbl_contrato.cont_numero, now(), now()
                            FROM tbl_contrato
                            WHERE tbl_contrato.cont_estado = 1
                            AND tbl_contrato.cont_fechaInicio <= pdatPeriodo
                            AND tbl_contrato.cont_fechaFin >= pdatPeriodo;

                        ELSE

                            IF ldatUltimo = pdatPeriodo AND pbolSobreescribir = 0 THEN
                              RETURN "02|Se esta intentado reprocesar un mes que ya se encuentra generado";
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

                      END;


                      -- Cargamos el maestro de personas
                      -- ----------------------------------------------------------------------------------------------------------------------------------------------
                      BEGIN

                         -- insertamos las personas que estan activas para el periodo de proceso
                         INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, created_at, updated_at)
                         SELECT pdatPeriodo, tbl_personas.IdPersona, tbl_contratos_personas.contrato_id, tbl_contratos_personas.idcontratista, now(), now()
                         FROM tbl_personas
                         INNER JOIN tbl_contratos_personas ON tbl_contratos_personas.idpersona = tbl_personas.Idpersona
                         INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                         INNER JOIN tbl_contrato_control_laboral ON tbl_contrato_control_laboral.contrato_id = tbl_contratos_personas.contrato_id and tbl_contrato_control_laboral.periodo = pdatPeriodo
                         WHERE ifnull(tbl_contratos_personas.FechaInicioFaena,tbl_contrato.cont_fechaInicio) <= LAST_DAY(pdatPeriodo);

                         -- insertamos personas finiquitadas que estén rechazadas o por cargar
                         INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, created_at, updated_at)
                         SELECT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, now(), now()
                         FROM tbl_personas
                         INNER JOIN tbl_documentos ON tbl_documentos.entidad = 3 and tbl_documentos.IdEntidad = tbl_personas.IdPersona and tbl_documentos.IdEstatus in (1,3)
                         INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.IdProceso = 4
                         INNER JOIN tbl_contrato ON tbl_documentos.contrato_id = tbl_contrato.contrato_id
                         INNER JOIN tbl_contrato_control_laboral ON tbl_contrato_control_laboral.contrato_id = tbl_documentos.contrato_id and tbl_contrato_control_laboral.periodo = pdatPeriodo;

                         -- insertamos finiquitadas en este periodo
                         INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, created_at, updated_at)
                         SELECT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, now(), now()
                         FROM tbl_personas
                         INNER JOIN tbl_documentos ON tbl_documentos.entidad = 3 and tbl_documentos.IdEntidad = tbl_personas.IdPersona and tbl_documentos.IdEstatus not in (1,3)
                         INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.IdProceso = 4
                         INNER JOIN tbl_contrato ON tbl_documentos.contrato_id = tbl_contrato.contrato_id
                         INNER JOIN tbl_contrato_control_laboral ON tbl_contrato_control_laboral.contrato_id = tbl_documentos.contrato_id and tbl_contrato_control_laboral.periodo = pdatPeriodo
                         INNER JOIN tbl_documento_valor ON tbl_documento_valor.IdDocumento = tbl_documentos.IdDocumento and tbl_documento_valor.IdTipoDocumentoValor = 171 and tbl_documento_valor.Valor BETWEEN pdatPeriodo AND LAST_DAY(pdatPeriodo);

                         -- insertamos finiquitadas en este periodo
                         INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, created_at, updated_at)
                         SELECT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, now(), now()
                         FROM tbl_personas
                         INNER JOIN tbl_documentos_rep_historico ON tbl_documentos_rep_historico.entidad = 3 and tbl_documentos_rep_historico.IdEntidad = tbl_personas.IdPersona
                         INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos_rep_historico.IdTipoDocumento and tbl_tipos_documentos.IdProceso = 4
                         INNER JOIN tbl_contrato ON tbl_documentos_rep_historico.contrato_id = tbl_contrato.contrato_id
                         INNER JOIN tbl_contrato_control_laboral ON tbl_contrato_control_laboral.contrato_id = tbl_documentos_rep_historico.contrato_id and tbl_contrato_control_laboral.periodo = pdatPeriodo
                         LEFT JOIN tbl_documento_valor_historico ON tbl_documento_valor_historico.IdDocumento = tbl_documentos_rep_historico.IdDocumento and tbl_documento_valor_historico.IdTipoDocumentoValor = 171 and tbl_documento_valor_historico.Valor BETWEEN pdatPeriodo AND LAST_DAY(pdatPeriodo);

                      END;

                      -- Cargamos el maestro de contratos
                      -- ----------------------------------------------------------------------------------------------------------------------------------------------
                      BEGIN

                         -- insertamos el maestro de contratos
                         INSERT INTO tbl_contrato_maestro(periodo, idcontratista, contrato_id, dotacion, trabajadores_con_o, costo_laboral, pasivo_laboral, created_at, updated_at)
                         SELECT DISTINCT pdatPeriodo, tbl_contrato.idcontratista, tbl_contrato.contrato_id, tbl_contrato.dotacion, 0, 0, 0, now(), now()
                         FROM (SELECT DISTINCT pdatPeriodo as periodo, tbl_contrato.idcontratista, tbl_contrato.contrato_id, tbl_contrato.cont_numero, tbl_contrato.cont_fechaFin, count(tbl_personas_maestro.idpersona) as dotacion
                               FROM tbl_contrato
                               LEFT JOIN tbl_personas_maestro ON tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id
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
      DB::unprepared('CREATE DEFINER=`root`@`localhost` FUNCTION `fnCierreLaboral`(pdatPeriodo DATE, pbolSobreescribir INT) RETURNS varchar(256) CHARSET latin1
BEGIN

                      DECLARE ldatUltimo DATE;
                      DECLARE lstrResultado VARCHAR(256);

                      -- si el periodo a ejecutar es el ultimo
                      SET ldatUltimo = NULL;
                      SET pbolSobreescribir = IFNULL(pbolSobreescribir,0);

                      -- Determinamos el maestro de contratos y los prepara para el proceso
                      -- ----------------------------------------------------------------------------------------------------------------------------------------------
                      BEGIN
                        SELECT Max(Periodo)
                        INTO ldatUltimo
                        FROM tbl_contrato_control_laboral;

                        IF ldatUltimo IS NULL THEN

                            -- primera vez que se ejecuta, insertamos todos los contratos vigentes para mes indicado como periodo
                            INSERT INTO tbl_contrato_control_laboral(periodo, contrato_id, cont_numero, created_at, updated_at)
                            SELECT pdatPeriodo, tbl_contrato.contrato_id, tbl_contrato.cont_numero, now(), now()
                            FROM tbl_contrato
                            WHERE tbl_contrato.cont_estado = 1
                            AND tbl_contrato.cont_fechaInicio <= pdatPeriodo
                            AND tbl_contrato.cont_fechaFin >= pdatPeriodo;

                        ELSE

                            IF ldatUltimo = pdatPeriodo AND pbolSobreescribir = 0 THEN
                              RETURN "02|Se esta intentado reprocesar un mes que ya se encuentra generado";
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

                      END;


                      -- Cargamos el maestro de personas
                      -- ----------------------------------------------------------------------------------------------------------------------------------------------
                      BEGIN

                         -- insertamos las personas que estan activas para el periodo de proceso
                         INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, created_at, updated_at)
                         SELECT pdatPeriodo, tbl_personas.IdPersona, tbl_contratos_personas.contrato_id, tbl_contratos_personas.idcontratista, now(), now()
                         FROM tbl_personas
                         INNER JOIN tbl_contratos_personas ON tbl_contratos_personas.idpersona = tbl_personas.Idpersona
                         INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
                         INNER JOIN tbl_contrato_control_laboral ON tbl_contrato_control_laboral.contrato_id = tbl_contratos_personas.contrato_id and tbl_contrato_control_laboral.periodo = pdatPeriodo
                         WHERE ifnull(tbl_contratos_personas.FechaInicioFaena,tbl_contrato.cont_fechaInicio) <= LAST_DAY(pdatPeriodo);

                         -- insertamos personas finiquitadas que estén rechazadas o por cargar
                         INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, created_at, updated_at)
                         SELECT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, now(), now()
                         FROM tbl_personas
                         INNER JOIN tbl_documentos ON tbl_documentos.entidad = 3 and tbl_documentos.IdEntidad = tbl_personas.IdPersona and tbl_documentos.IdEstatus in (1,3)
                         INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.IdProceso = 4
                         INNER JOIN tbl_contrato ON tbl_documentos.contrato_id = tbl_contrato.contrato_id
                         INNER JOIN tbl_contrato_control_laboral ON tbl_contrato_control_laboral.contrato_id = tbl_documentos.contrato_id and tbl_contrato_control_laboral.periodo = pdatPeriodo;

                         -- insertamos finiquitadas en este periodo
                         INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, created_at, updated_at)
                         SELECT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, now(), now()
                         FROM tbl_personas
                         INNER JOIN tbl_documentos ON tbl_documentos.entidad = 3 and tbl_documentos.IdEntidad = tbl_personas.IdPersona and tbl_documentos.IdEstatus not in (1,3)
                         INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.IdProceso = 4
                         INNER JOIN tbl_contrato ON tbl_documentos.contrato_id = tbl_contrato.contrato_id
                         INNER JOIN tbl_contrato_control_laboral ON tbl_contrato_control_laboral.contrato_id = tbl_documentos.contrato_id and tbl_contrato_control_laboral.periodo = pdatPeriodo
                         INNER JOIN tbl_documento_valor ON tbl_documento_valor.IdDocumento = tbl_documentos.IdDocumento and tbl_documento_valor.IdTipoDocumentoValor = 171 and tbl_documento_valor.Valor BETWEEN pdatPeriodo AND LAST_DAY(pdatPeriodo);

                         -- insertamos finiquitadas en este periodo
                         INSERT INTO tbl_personas_maestro(periodo, idpersona, contrato_id, idcontratista, created_at, updated_at)
                         SELECT pdatPeriodo, tbl_personas.IdPersona, tbl_contrato.contrato_id, tbl_contrato.idcontratista, now(), now()
                         FROM tbl_personas
                         INNER JOIN tbl_documentos_rep_historico ON tbl_documentos_rep_historico.entidad = 3 and tbl_documentos_rep_historico.IdEntidad = tbl_personas.IdPersona
                         INNER JOIN tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos_rep_historico.IdTipoDocumento and tbl_tipos_documentos.IdProceso = 4
                         INNER JOIN tbl_contrato ON tbl_documentos_rep_historico.contrato_id = tbl_contrato.contrato_id
                         INNER JOIN tbl_contrato_control_laboral ON tbl_contrato_control_laboral.contrato_id = tbl_documentos_rep_historico.contrato_id and tbl_contrato_control_laboral.periodo = pdatPeriodo
                         LEFT JOIN tbl_documento_valor_historico ON tbl_documento_valor_historico.IdDocumento = tbl_documentos_rep_historico.IdDocumento and tbl_documento_valor_historico.IdTipoDocumentoValor = 171 and tbl_documento_valor_historico.Valor BETWEEN pdatPeriodo AND LAST_DAY(pdatPeriodo);

                      END;

                      -- Cargamos el maestro de contratos
                      -- ----------------------------------------------------------------------------------------------------------------------------------------------
                      BEGIN

                         -- insertamos el maestro de contratos
                         INSERT INTO tbl_contrato_maestro(periodo, idcontratista, contrato_id, dotacion, trabajadores_con_o, costo_laboral, pasivo_laboral, created_at, updated_at)
                         SELECT DISTINCT pdatPeriodo, tbl_contrato.idcontratista, tbl_contrato.contrato_id, count(tbl_personas_maestro.idpersona), 0, 0, 0, now(), now()
                         FROM tbl_contrato
                         LEFT JOIN tbl_personas_maestro ON tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id
                         WHERE (tbl_contrato.cont_fechaInicio <= LAST_DAY(pdatPeriodo) AND tbl_contrato.cont_FechaFin >= pdatPeriodo AND IFNULL(tbl_contrato.cont_estado,1) != 2)
                         OR EXISTS (SELECT 1 FROM tbl_personas_maestro WHERE tbl_personas_maestro.contrato_id = tbl_contrato.contrato_id)
                         OR EXISTS (SELECT 1
                                    FROM tbl_documentos
                                    INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.ControlCheckLaboral = 1
                                    WHERE tbl_documentos.Entidad = 2
                                    AND tbl_documentos.FechaEmision <= pdatPeriodo
                                    AND tbl_documentos.IdEntidad = tbl_contrato.contrato_id
                                    AND tbl_documentos.IdEstatus != 5)
                         GROUP BY tbl_contrato.idcontratista, tbl_contrato.contrato_id;

                      END;

                      RETURN "01|Proceso de cierre finalizado satisfactoriamente";

                    END');
    }
}
