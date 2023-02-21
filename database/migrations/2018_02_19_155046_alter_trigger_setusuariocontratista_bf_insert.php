<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerSetusuariocontratistaBfInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `setUsuarioContratista`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `setUsuarioContratista` 
                        BEFORE INSERT ON `tbl_contrato` FOR EACH ROW
                        BEGIN
                          SET NEW.entry_by_access := (SELECT entry_by_access FROM tbl_contratistas WHERE IdContratista = NEW.IdContratista);
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
        DB::unprepared("DROP TRIGGER IF EXISTS `setUsuarioContratista`;");
    }
}
