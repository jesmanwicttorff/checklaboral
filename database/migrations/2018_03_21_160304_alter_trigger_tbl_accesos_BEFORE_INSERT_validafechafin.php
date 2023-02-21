<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerTblAccesosBEFOREINSERTValidafechafin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_accesos_BEFORE_INSERT`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `tbl_accesos_BEFORE_INSERT` 
                        BEFORE INSERT ON `tbl_accesos` FOR EACH ROW
                        BEGIN
                              DECLARE lintIdEstatus INT;
                            DECLARE lstrDataRut varchar(20);
                            DECLARE lstrDataNombres varchar(50);
                            DECLARE lstrDataApellidos varchar(50);
                               
                                IF new.FechaFinal < NOW() THEN
                                    SET NEW.IdEstatusUsuario := 2;
                                END IF;
                               
                                IF new.IdTipoAcceso = 1 THEN
                                    SET lintIdEstatus := fnComprobarAccesos(NEW.IdPersona);
    
                                    IF lintIdEstatus = 2 THEN
                                        SET NEW.IdEstatus := 2;
                                        SET NEW.IdEstatusUsuario := 2;
                                    ELSE 
                                        SET NEW.IdEstatus := 1;
                                        SET NEW.IdEstatusUsuario := 1;
                                    END IF;
                                    
                                    SELECT rut, nombres, apellidos
                                    INTO lstrDataRut, lstrDataNombres, lstrDataApellidos
                                    FROM tbl_personas
                                    WHERE tbl_personas.IdPersona = NEW.IdPersona;
                                    
                                    SET NEW.data_rut = lstrDataRut;
                                    SET NEW.data_nombres = lstrDataNombres;
                                    SET NEW.data_apellidos = lstrDataApellidos;
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
        DB::unprepared("DROP TRIGGER IF EXISTS `tbl_accesos_BEFORE_INSERT`;");
    }
}
