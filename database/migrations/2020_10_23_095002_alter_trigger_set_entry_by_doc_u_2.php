<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerSetEntryByDocU2 extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `setENtryByDocU`;");
        DB::unprepared("CREATE TRIGGER `setENtryByDocU` 
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
    END");
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
