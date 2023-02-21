<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAccesosLogAgregoDatarutTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_accesos_log_BEFORE_INSERT`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `tbl_accesos_log_BEFORE_INSERT` BEFORE INSERT ON `tbl_accesos_log` FOR EACH ROW
                BEGIN
                DECLARE lstrDataRut varchar(20);
                DECLARE lstrDataNombres varchar(50);
                DECLARE lstrDataApellidos varchar(50);

                    IF NEW.IdTipoEntidad = 1 THEN 
                        SELECT tbl_accesos.data_rut, tbl_accesos.data_nombres, tbl_accesos.data_apellidos
                        INTO lstrDataRut, lstrDataNombres, lstrDataApellidos
                        FROM tbl_accesos
                        WHERE tbl_accesos.IdAcceso = NEW.IdAcceso;

                        SET NEW.data_rut = lstrDataRut;
                        SET NEW.data_nombres = lstrDataNombres;
                        SET NEW.data_apellidos = lstrDataApellidos;
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
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_accesos_log_BEFORE_INSERT`;");
    }
}
