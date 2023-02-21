<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrgPersonasAcreditacionBfInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::unprepared("DROP TRIGGER IF EXISTS `tbl_personas_acreditacion_before_insert`;");
      DB::unprepared("CREATE TRIGGER `tbl_personas_acreditacion_before_insert` BEFORE INSERT ON `tbl_personas_acreditacion` FOR EACH ROW BEGIN
      	DECLARE contrato INT;
      	SELECT contrato_id INTO contrato FROM tbl_contratos_personas WHERE idpersona= NEW.idpersona;
      	if NEW.acreditacion IS NOT NULL then
      		INSERT INTO tbl_historial_acreditacion_personas (IdPersona, acreditacion,entry_by,IdEstatus, fecha, contrato_id) VALUES (NEW.idpersona, NEW.acreditacion, NEW.entry_by, NEW.idestatus, CURRENT_TIMESTAMP(),contrato);
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
