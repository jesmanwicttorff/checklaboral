<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriUpdateContratoInsertLod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        DB::unprepared("DROP TRIGGER IF EXISTS `setUsuarioContratistaUpd`;");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `setUsuarioContratistaUpd` BEFORE UPDATE ON `tbl_contrato` 
                        FOR EACH ROW BEGIN


                     DECLARE exists_row INT;
                     SET exists_row := (SELECT MAX(id) FROM lod_tbl_lod WHERE contrato_id = new.contrato_id);

                      
                      IF IFNULL(exists_row ,0) = 0 THEN
                        INSERT INTO lod_tbl_lod (contrato_id, contratista_id, supervisor_id, STATUS, created_at, updated_at)
                        VALUES (new.contrato_id, new.entry_by_access, new.admin_id, 1, new.createdOn, new.updatedOn);
                      ELSE 
                        UPDATE lod_tbl_lod a
                        SET a.supervisor_id  = NEW.admin_id,
                            a.contratista_id = NEW.entry_by_access
                        WHERE a.contrato_id = NEW.contrato_id;
                      END IF;
                      
                    END");

    }
}
