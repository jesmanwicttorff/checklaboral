<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVwKpiActivosView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		DB::statement("
			CREATE 
				 OR REPLACE ALGORITHM = UNDEFINED 
				DEFINER = `root`@`localhost` 
				SQL SECURITY DEFINER
			VIEW `vw_kpi_activos` AS
				SELECT 
					`tbl_kpis`.`IdKpi` AS `IdKpi`,
					`tbl_kpis`.`contrato_id` AS `contrato_id`,
					`tbl_kpis`.`IdTipo` AS `IdTipo`,
					`tbl_kpis`.`Nombre` AS `Nombre`,
					`tbl_kpis`.`Descripcion` AS `Descripcion`,
					`tbl_kpis`.`IdUnidad` AS `IdUnidad`,
					`tbl_kpis`.`Formula` AS `Formula`,
					`tbl_kpis`.`RangoSuperior` AS `RangoSuperior`,
					`tbl_kpis`.`RangoInferior` AS `RangoInferior`,
					`tbl_kpis`.`IdEstatus` AS `IdEstatus`,
					`tbl_kpis`.`entry_by` AS `entry_by`,
					`tbl_kpis`.`updated_by` AS `updated_by`,
					`tbl_kpis`.`created_at` AS `created_at`,
					`tbl_kpis`.`updated_at` AS `updated_at`
				FROM
					`tbl_kpis`
				WHERE
					(`tbl_kpis`.`IdEstatus` = 1);
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
		DB::statement('DROP VIEW IF EXISTS vw_kpi_activos');
    }
}
