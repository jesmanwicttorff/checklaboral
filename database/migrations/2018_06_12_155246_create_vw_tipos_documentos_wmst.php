<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVwTiposDocumentosWmst extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
			CREATE 
				 OR REPLACE ALGORITHM = UNDEFINED 
				DEFINER = `root`@`localhost` 
				SQL SECURITY DEFINER
			VIEW `vw_tipos_documentos_wmst` AS
				SELECT 
					`tbl_tipos_documentos`.`IdTipoDocumento` AS `IdTipoDocumento`,
					`tbl_tipos_documentos`.`Descripcion` AS `Descripcion`
				FROM
					`tbl_tipos_documentos`
				WHERE
					(`tbl_tipos_documentos`.`IdTipoDocumento` not in (77,79));
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS vw_tipos_documentos_wmst');
    }
}
