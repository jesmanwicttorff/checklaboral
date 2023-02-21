<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerTblAccesosBEFOREUPDATE extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
 
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_accesos_BEFORE_UPDATE`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER  `tbl_accesos_BEFORE_UPDATE`
        BEFORE UPDATE ON `tbl_accesos` FOR EACH ROW BEGIN
              #  IF OLD.FechaFinal != NEW.FechaFinal AND NEW.Updated_by IS NULL THEN
              #      SET NEW.IdEstatusUsuario = NEW.IdEstatus;
              #  END IF;
              #  IF NEW.FechaFinal  < CURDATE() AND NEW.Updated_by IS NULL THEN
              #      SET NEW.IdEstatusUsuario = 2;
              #  END IF;
        END");
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    
    public function down()
    {
        //
    }
}
