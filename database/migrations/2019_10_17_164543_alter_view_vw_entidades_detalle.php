<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterViewVwEntidadesDetalle extends Migration
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
                      VIEW `vw_entidades_detalle` AS
                          select 1 AS `entidad`,`tbl_contratistas`.`IdContratista` AS `identidad`,concat(`tbl_contratistas`.`RUT`,\'   \',`tbl_contratistas`.`RazonSocial`) AS `detalle` from `tbl_contratistas`
                          union all
                          select 2 AS `entidad`,`tbl_contrato`.`contrato_id` AS `identidad`,concat(`tbl_contrato`.`cont_nombre`,\'   \',`tbl_contrato`.`cont_numero`) AS `detalle` 
                          from `tbl_contrato`
                          union all select 3 AS `entidad`,`tbl_personas`.`IdPersona` AS `identidad`,concat(`tbl_personas`.`RUT`,\'   \',`tbl_personas`.`Nombres`,\'   \',`tbl_personas`.`Apellidos`) AS `detalle`
                          from `tbl_personas`
                          union all select 6 AS `entidad`,`tbl_centro`.`IdCentro` AS `identidad`,`tbl_centro`.`Descripcion` AS `detalle`
                          from `tbl_centro`
                          union all SELECT 10 AS entidad, tbl_activos_data.contrato_id AS identidad, tbl_activos.Descripcion COLLATE utf8_general_ci AS detalle
                          FROM tbl_activos_data JOIN tbl_activos ON tbl_activos_data.IdActivo = tbl_activos.IdActivo;
      ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::statement('CREATE OR REPLACE ALGORITHM = UNDEFINED
                          DEFINER = `root`@`localhost`
                          SQL SECURITY DEFINER
                      VIEW `vw_entidades_detalle` AS
                          select 1 AS `entidad`,`tbl_contratistas`.`IdContratista` AS `identidad`,concat(`tbl_contratistas`.`RUT`,\'   \',`tbl_contratistas`.`RazonSocial`) AS `detalle` from `tbl_contratistas`
                          union all
                          select 2 AS `entidad`,`tbl_contrato`.`contrato_id` AS `identidad`,concat(`tbl_contrato`.`cont_nombre`,\'   \',`tbl_contrato`.`cont_numero`) AS `detalle`
                          from `tbl_contrato`
                          union all select 3 AS `entidad`,`tbl_personas`.`IdPersona` AS `identidad`,concat(`tbl_personas`.`RUT`,\'   \',`tbl_personas`.`Nombres`,\'   \',`tbl_personas`.`Apellidos`) AS `detalle`
                          from `tbl_personas`
                          union all select 6 AS `entidad`,`tbl_centro`.`IdCentro` AS `identidad`,`tbl_centro`.`Descripcion` AS `detalle` from `tbl_centro`;
      ');
    }
}
