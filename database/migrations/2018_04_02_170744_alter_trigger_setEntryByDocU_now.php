<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerSetEntryByDocUNow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `setENtryByDocU`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `setENtryByDocU` 
                        BEFORE UPDATE ON `tbl_documentos` FOR EACH ROW
                        BEGIN
                         
                         declare lintIdResult int;
	IF (NEW.Entidad = 1) THEN
		SET NEW.entry_by_access := (SELECT tbl_contratistas.entry_by_access
                                FROM tbl_contratistas
                                WHERE tbl_contratistas.IdContratista = NEW.IdEntidad);
    ELSEIF (NEW.Entidad = 2) THEN
		SET NEW.entry_by_access := (SELECT entry_by_access
                                FROM tbl_contrato
                                WHERE tbl_contrato.contrato_id = NEW.IdEntidad);
	ELSEIF (NEW.Entidad = 6) THEN
		SET NEW.entry_by_access := (SELECT entry_by_access
									FROM tbl_contrato
                                    WHERE tbl_contrato.contrato_id = NEW.contrato_id);
	ELSEIF (NEW.Entidad >= 10) THEN
		SET NEW.entry_by_access := (SELECT entry_by_access
									FROM tbl_contrato
                                    WHERE tbl_contrato.contrato_id = NEW.contrato_id);
	END IF;

    
	IF (NEW.IdEstatus=5) THEN
      SELECT COUNT(*)
      INTO lintIdResult
      FROM tbl_tipos_documentos
      WHERE tbl_tipos_documentos.bloqueaacceso = 'SI'
      AND tbl_tipos_documentos.vigencia = 1
      AND tbl_tipos_documentos.idtipodocumento = NEW.IdTipoDocumento;
      IF (lintIdResult > 0) then
        IF ifnull(NEW.FechaVencimiento,'0000-00-00') != '0000-00-00' and ifnull(NEW.FechaVencimiento,'0000-00-00 00:00:00') != '0000-00-00 00:00:00' then
			IF (NEW.FechaVencimiento < current_date() ) THEN
                SELECT COUNT(*) 
                INTO lintIdResult
                FROM tbl_documentos m 
                WHERE m.iddocumento = NEW.IdDocumentoRelacion 
                AND m.FechaVencimiento > NEW.FechaVencimiento;
                IF (lintIdResult = 0) then
					SET NEW.IdEstatusDocumento := 2;
                END IF;
			ELSEIF (NEW.FechaVencimiento >= current_date() ) THEN
				SET NEW.IdEstatusDocumento := 1;
			END IF;
        END IF;
      END if;
	END IF;

    
    IF (NEW.IdEstatus != OLD.IdEstatus) THEN

		
		IF (NEW.IdTipoDocumento=70 AND NEW.IdEstatus=5) THEN

			
			INSERT INTO tbl_equifax_consolidado (IdContratista, FechaEmision, Predictor, NumDocImpago, MontoImpago)
			SELECT tbl_documentos.IdEntidad, tbl_documentos.FechaEmision, a.valor AS pred_emp, b.valor as tot_doc_imp, c.valor as monto_imp
			FROM tbl_documentos
			INNER JOIN (SELECT a.IdDocumento, a.valor
						FROM tbl_documento_valor a
						WHERE a.IdTipoDocumentoValor = 126
						AND a.IdDocumento = NEW.IdDocumento) as a on a.IdDocumento = tbl_documentos.IdDocumento
			INNER JOIN (SELECT a.IdDocumento, a.valor
						FROM tbl_documento_valor a
						WHERE a.IdTipoDocumentoValor = 132
						AND a.IdDocumento = NEW.IdDocumento) as b on b.IdDocumento = tbl_documentos.IdDocumento
			INNER JOIN (SELECT a.IdDocumento, a.valor
						FROM tbl_documento_valor a
						WHERE a.IdTipoDocumentoValor = 133
						AND a.IdDocumento = NEW.IdDocumento) as c on c.IdDocumento = tbl_documentos.IdDocumento
			WHERE tbl_documentos.IdDocumento = NEW.IdDocumento
			AND tbl_documentos.entidad = 1
			AND NOT EXISTS (SELECT 1 FROM tbl_equifax_consolidado WHERE tbl_equifax_consolidado.IdContratista = tbl_documentos.IdEntidad AND tbl_documentos.FechaEmision = tbl_equifax_consolidado.FechaEmision);


			
            INSERT INTO tbl_morosidad ( contrato_id, fecha_Eval, per_moros )
            SELECT tbl_contrato.contrato_id,
				   m.fecha,
				   CAST((2/1000000000*(media_exp*media_exp*media_exp)-3/1000000*(media_exp*media_exp)+0.0002*media_exp+0.9706) AS DECIMAL(4,3)) morosidad
			FROM   (SELECT IdContratista,
						   (SELECT MAX(FechaEmision) FROM tbl_equifax_consolidado WHERE tbl_equifax_consolidado.IdContratista = NEW.IdEntidad ) fecha,  SUM(Predictor*dif) / SUM(dif) media_exp
			FROM  (SELECT IdContratista, FechaEmision, Predictor FROM tbl_equifax_consolidado WHERE tbl_equifax_consolidado.IdContratista = NEW.IdEntidad ) a
			INNER JOIN
			(SELECT DISTINCT fecha_mes, TIMESTAMPDIFF(MONTH,DATE_ADD(CAST((SELECT MAX(FechaEmision) FROM tbl_equifax_consolidado WHERE tbl_equifax_consolidado.IdContratista = NEW.IdEntidad )AS DATE), INTERVAL -11 MONTH),fecha_mes)+1 dif
			 FROM dim_tiempo WHERE fecha_mes BETWEEN DATE_ADD(CAST((SELECT MAX(FechaEmision) FROM tbl_equifax_consolidado WHERE tbl_equifax_consolidado.IdContratista = NEW.IdEntidad )AS DATE), INTERVAL -11 MONTH)
			 AND ( SELECT MAX(FechaEmision) FROM tbl_equifax_consolidado WHERE tbl_equifax_consolidado.IdContratista = NEW.IdEntidad )) b
			 ON a.FechaEmision = b.fecha_mes
			 GROUP BY 1,2
			) m
            INNER JOIN tbl_contrato ON M.IdContratista = tbl_contrato.IdContratista AND tbl_contrato.cont_estado = 1 AND tbl_contrato.id_extension = 1
			WHERE m.IdContratista = NEW.IdEntidad;

        
        ELSEIF (NEW.IdTipoDocumento=78 AND (NEW.IdEstatus=5 OR NEW.IdEstatus=2) ) THEN
			SET NEW.IdEstatus = 5;
			INSERT INTO tbl_evalcontratistas (contrato_id, eval_fecha, eval_puntaje, eval_comentario, eval_fecha_evaluacion)
			SELECT NEW.contrato_id as contrato_id,
				   NEW.FechaEmision as eval_fecha,
				   tbl_encuestas.ResumenEvaluacion as eval_puntaje,
				   tbl_encuestas.Comentario as eval_comentario,
                   NEW.FechaEmision
			FROM tbl_encuestas
			WHERE tbl_encuestas.IdDocumento = NEW.IdDocumento
            AND NOT EXISTS (SELECT 1
							  FROM tbl_evalcontratistas
							  WHERE tbl_evalcontratistas.contrato_id = NEW.contrato_id
			 				  AND tbl_evalcontratistas.eval_fecha = NEW.FechaEmision);

      ELSEIF (NEW.IdTipoDocumento=84 AND (NEW.IdEstatus=5 OR NEW.IdEstatus=2) ) THEN
			SET NEW.IdEstatus = 5;
			INSERT INTO tbl_calidad_adm_cont (contrato_id, fec_rev, resultado, fec_rev_evaluacion)
			SELECT NEW.contrato_id as contrato_id,
				   NEW.FechaEmision as fec_rev,
				   tbl_encuestas.ResumenEvaluacion as resultado,
                   NEW.FechaEmision
			FROM tbl_encuestas
			WHERE tbl_encuestas.IdDocumento = NEW.IdDocumento
            AND NOT EXISTS (SELECT 1
							  FROM tbl_calidad_adm_cont
							  WHERE tbl_calidad_adm_cont.contrato_id = NEW.contrato_id
			 				  AND tbl_calidad_adm_cont.fec_rev = NEW.FechaEmision);

        END IF;

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
        DB::unprepared("DROP TRIGGER IF EXISTS `setENtryByDocU`;");
    }
}
