<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrgPersonasAcreditacionBfUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::unprepared("DROP TRIGGER IF EXISTS `tbl_personas_acreditacion_before_update`;");
      DB::unprepared("CREATE TRIGGER `tbl_personas_acreditacion_before_update` BEFORE UPDATE ON `tbl_personas_acreditacion` FOR EACH ROW BEGIN
      	DECLARE contrato INT;
      	SELECT contrato_id INTO contrato FROM tbl_contratos_personas WHERE idpersona= NEW.idpersona;
      	if NEW.acreditacion IS NOT NULL then
      		INSERT INTO tbl_historial_acreditacion_personas (IdPersona, acreditacion,entry_by,IdEstatus, fecha,contrato_id) VALUES (NEW.idpersona, NEW.acreditacion, NEW.entry_by, NEW.idestatus, CURRENT_TIMESTAMP(),contrato);
      	END if;  	
      	if NEW.acreditacion IS NULL then
      		if NEW.idestatus=2 then
      			INSERT INTO tbl_historial_acreditacion_personas (IdPersona, acreditacion,entry_by,IdEstatus, fecha,contrato_id) VALUES (NEW.idpersona, NEW.acreditacion, NEW.entry_by, NEW.idestatus, CURRENT_TIMESTAMP(),contrato);
      		END if;
      	END if;
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
