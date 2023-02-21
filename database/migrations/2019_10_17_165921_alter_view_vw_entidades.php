<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterViewVwEntidades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::statement('CREATE OR REPLACE ALGORITHM = UNDEFINED
                          DEFINER = `root`@`localhost`
                          SQL SECURITY DEFINER
                      VIEW `vw_entidades` AS
                          select `tbl_entidades`.`IdEntidad` AS `IdEntidad`,`tbl_entidades`.`Entidad` AS `Entidad` from `tbl_entidades`;
      ');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::statement('select `tbl_entidades`.`IdEntidad` AS `IdEntidad`,`tbl_entidades`.`Entidad` AS `Entidad` from `tbl_entidades` union all select `tbl_activos`.`IdActivo` AS `IdEntidad`,`tbl_activos`.`Descripcion` AS `Entidad` from `tbl_activos` ;
      ');
    }
}
