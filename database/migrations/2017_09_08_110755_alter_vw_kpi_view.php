<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVwKpiView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE ALGORITHM = UNDEFINED 
                            DEFINER = `root`@`localhost` 
                            SQL SECURITY DEFINER
                        VIEW `vw_kpi` AS
                            SELECT 
                                `tbl_kpis`.`contrato_id` AS `contrato_id`,
                                `tbl_kpis_detalles`.`Fecha` AS `kpiDet_fecha`,
                                AVG((CASE
                                    WHEN (`tbl_kpis_detalles`.`Resultado` < 0) THEN 0
                                    WHEN (`tbl_kpis_detalles`.`Resultado` > 100) THEN 100
                                    ELSE `tbl_kpis_detalles`.`Resultado`
                                END)) AS `Valor`
                            FROM
                                (`tbl_kpis`
                                JOIN `tbl_kpis_detalles` ON ((`tbl_kpis`.`IdKpi` = `tbl_kpis_detalles`.`IdKpi`)))
                            WHERE
                                (`tbl_kpis`.`IdEstatus` = 1 )
                            GROUP BY `tbl_kpis`.`contrato_id` , `tbl_kpis_detalles`.`Fecha`;
        ");
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
                        VIEW `vw_kpi` AS
                            SELECT 
                                `tbl_kpigral`.`contrato_id` AS `contrato_id`,
                                `tbl_kpimensual`.`kpiDet_fecha` AS `kpiDet_fecha`,
                                ((SUM((CASE
                                    WHEN
                                        (`tbl_kpigral`.`kpi_tipo_calc` = 1)
                                    THEN
                                        (CASE
                                            WHEN (`tbl_kpimensual`.`kpiDet_puntaje` >= `tbl_kpimensual`.`kpiDet_meta`) THEN 1
                                            ELSE 0
                                        END)
                                    WHEN
                                        (`tbl_kpigral`.`kpi_tipo_calc` = 2 )
                                    THEN
                                        (CASE
                                            WHEN (`tbl_kpimensual`.`kpiDet_puntaje` <= `tbl_kpimensual`.`kpiDet_meta`) THEN 1
                                            ELSE 0
                                        END)
                                    WHEN
                                        (`tbl_kpigral`.`kpi_tipo_calc` = 3)
                                    THEN
                                        (CASE
                                            WHEN (`tbl_kpimensual`.`kpiDet_puntaje` BETWEEN `tbl_kpimensual`.`kpiDet_min` AND `tbl_kpimensual`.`kpiDet_max`) THEN 1
                                            ELSE 0
                                        END)
                                    WHEN (`tbl_kpigral`.`kpi_tipo_calc` = 4) THEN 1
                                END)) / COUNT(DISTINCT `tbl_kpigral`.`id_kpi`)) * 100) AS `Valor`
                            FROM
                                (`tbl_kpigral`
                                JOIN `tbl_kpimensual` ON ((`tbl_kpigral`.`id_kpi` = `tbl_kpimensual`.`id_kpi`)))
                            WHERE
                                (`tbl_kpimensual`.`kpiDet_puntaje` IS NOT NULL)
                            GROUP BY `tbl_kpigral`.`contrato_id` , `tbl_kpimensual`.`kpiDet_fecha`;
        ');
    }
}
