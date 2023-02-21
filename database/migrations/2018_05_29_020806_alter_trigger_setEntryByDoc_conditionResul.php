<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerSetEntryByDocConditionResul extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `setENtryByDoc`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `setENtryByDoc`  
                        BEFORE INSERT ON `tbl_documentos` FOR EACH ROW 
                        BEGIN 
                          
                            DECLARE int_period INT;

                            SET int_period := (
                        
                            SELECT Periodicidad
                        
                            FROM tbl_tipos_documentos
                        
                            
                        
                            WHERE IdTipoDocumento = NEW.IdTipoDocumento AND Vigencia = 2);
                        
                            
                        
                            IF ((NEW.IdTipoDocumento = 6 and NEW.Resultado != '-') OR (NEW.IdTipoDocumento = 8)) THEN
                        
                                SET NEW.IdEstatus = 7;
                        
                            END IF;
                        
                            
                        
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
                            
                            ELSE
                        
                                SET NEW.entry_by_access := (SELECT entry_by_access 
                        
                                                        FROM tbl_personas
                        
                                                        WHERE tbl_personas.IdPersona = NEW.IdEntidad);
                        
                            END IF;
                        
                            
                  
                        if (NEW.FechaVencimiento is null) then
                        
                            if int_period = 1 THEN
                        
                            Set NEW.FechaVencimiento = DATE_FORMAT(DATE_ADD(DATE_ADD(NEW.createdOn, INTERVAL 1 MONTH), INTERVAL -DAY(NEW.createdOn) DAY),'%Y-%m-%d');
                        
                            elseIF int_period = 2 THEN
                        
                            Set NEW.FechaVencimiento = CASE WHEN MONTH(NEW.createdOn) BETWEEN 1 AND 3 THEN CONCAT(YEAR(NEW.createdOn),'-06-30')
                        
                                            WHEN MONTH(NEW.createdOn) BETWEEN 4 AND 6 THEN CONCAT(YEAR(NEW.createdOn),'-09-30')
                        
                                            WHEN MONTH(NEW.createdOn) BETWEEN 7 AND 9 THEN CONCAT(YEAR(NEW.createdOn),'-12-31')
                        
                                            WHEN MONTH(NEW.createdOn) BETWEEN 10 AND 12 THEN CONCAT(YEAR(NEW.createdOn)+1,'-03-31')
                        
                                           END;
                        
                            ELSEIF int_period = 3 THEN
                        
                            SET NEW.FechaVencimiento = CASE WHEN MONTH(NEW.createdOn) BETWEEN 1 AND 6 THEN CONCAT(YEAR(NEW.createdOn),'-12-31')
                        
                                            WHEN MONTH(NEW.createdOn) BETWEEN 7 AND 12 THEN CONCAT(YEAR(NEW.createdOn)+1,'-06-30')
                        
                                           END;
                        
                            ELSEIF int_period = 4 THEN
                        
                            SET NEW.FechaVencimiento = DATE_FORMAT(DATE_ADD(DATE_ADD(NEW.createdOn, INTERVAL 1 YEAR), INTERVAL -DAY(NEW.createdOn) DAY),'%Y-%m-%d');
                        
                            END IF;
                            
                            
                            IF NEW.IdTipoDocumento != 78 AND NEW.IdTipoDocumento != 84 THEN
                            set NEW.FechaEmision = DATE_FORMAT(DATE_ADD(NEW.createdOn, INTERVAL -1*(DAY(NEW.createdOn)-1) DAY),'%Y-%m-%d');
                            END IF;
                        
                        END IF;
                        
                        IF NEW.IdTipoDocumento IN ('26', '44', '66') AND NEW.Entidad = 1 THEN
                            SET NEW.IdEstatus := 5;
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
        DB::unprepared("DROP TRIGGER IF EXISTS `setENtryByDoc`;");
    }
}
