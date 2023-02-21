<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class visionxpartidadetalle extends Sximo  {
	
	protected $table = 'tbl_contratos_items';
	protected $primaryKey = 'IdContratoItem';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){

		return " select *
    from (select case when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) = 0 and ifnull(b.Identificacion,0) != 0 then 
         concat(b.Identificacion,' ',b.descripcion)
     when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then 
         concat(c.Identificacion,' ',c.descripcion)
     when ifnull(d.Identificacion,0) != 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then 
         concat(d.Identificacion,' ',d.descripcion)
   else
       a.Identificacion
   end as IdentificacionParent, case when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) = 0 and ifnull(b.Identificacion,0) != 0 then 
        concat(b.Identificacion,'.',a.Identificacion) 
      when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then 
          concat(c.Identificacion,'.',b.Identificacion,'.',a.Identificacion) 
            when ifnull(d.Identificacion,0) != 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then 
                concat(d.Identificacion,'.',c.Identificacion,'.',b.Identificacion,'.',a.Identificacion) 
          else 
        a.Identificacion 
          end as Identificacion,
       a.IdContratoItem,
       a.IdParent,
       a.descripcion,
       a.cantidad, 
       a.monto,
       e.Descripcion as Unidad, 
       e.Abreviacion as UnidadAbreviacion, 
       (a.cantidad*a.monto) as total_contrato,
       a.planacumulado_cantidad,
       a.planacumulado,
       a.realacumulado_cantidad,
       a.realacumulado,
       (a.realacumulado_cantidad*a.realacumulado) as total_acumulado_real,
       (a.planacumulado_cantidad*a.planacumulado) as total_acumulado_plan,
       (1 - ( (a.realacumulado_cantidad*a.realacumulado)/(a.planacumulado_cantidad*a.planacumulado) ) ) as desviacion_acumulada,
       a.mesrealacumulado_cantidad,
       a.mesrealacumulado,
       a.contrato_id,
       a.mesplanacumulado_cantidad,
       a.mesplanacumulado,
       (a.mesrealacumulado_cantidad*a.mesrealacumulado) as total_mes_real,
       (a.mesplanacumulado_cantidad*a.mesplanacumulado) as total_mes_plan,
       (1 - ( (a.mesrealacumulado_cantidad*a.mesrealacumulado)/(a.mesplanacumulado_cantidad*a.mesplanacumulado) ) ) as desviacion_mes
from ( SELECT
        `tbl_contratos_items`.`IdContratoItem` AS `IdContratoItem`,
        `tbl_contratos_items`.`IdParent` AS `IdParent`,
        `tbl_contratos_items`.`contrato_id` AS `contrato_id`,
        `tbl_contratos_items`.`Identificacion` AS `Identificacion`,
        `tbl_contratos_items`.`Descripcion` AS `descripcion`,
        `tbl_contratos_items`.`Cantidad` AS `cantidad`,
        `tbl_contratos_items`.`Monto` AS `monto`,
        `tbl_contratos_items`.`IdUnidad` AS `IdUnidad`,
        (`tbl_contratos_items`.`Cantidad` * `tbl_contratos_items`.`Monto`) AS `subtotal`,
        ultimomes.mes,
        `tbl_contratos_items_p`.`Cantidad` AS `planacumulado_cantidad`,
        `tbl_contratos_items_p`.`Monto` AS `planacumulado`,
        `tbl_contratos_items_r`.`Cantidad` AS `realacumulado_cantidad`,
        `tbl_contratos_items_r`.`Monto` AS `realacumulado`,
        rm.`Cantidad` AS `mesrealacumulado_cantidad`,
        rm.`Monto` AS `mesrealacumulado`,
        pm.`Cantidad` AS `mesplanacumulado_cantidad`,
        pm.`Monto` AS `mesplanacumulado`
    FROM
        `tbl_contratos_items`
        LEFT JOIN (select contrato_id, max(mes) mes from tbl_contratos_items_r group by contrato_id) as ultimomes on tbl_contratos_items.contrato_id = ultimomes.contrato_id
        LEFT JOIN (select `tbl_contratos_items_p`.`Mes`, `tbl_contratos_items_p`.`IdItem`, SUM(`tbl_contratos_items_p`.`Cantidad`) as Cantidad, MAX(`tbl_contratos_items_p`.`Monto`) as monto from `tbl_contratos_items_p` GROUP BY `tbl_contratos_items_p`.`Mes`, `tbl_contratos_items_p`.`IdItem`) as tbl_contratos_items_p ON `tbl_contratos_items`.`IdContratoItem` = `tbl_contratos_items_p`.`IdItem` AND `tbl_contratos_items_p`.`Mes` <= ultimomes.mes
        LEFT JOIN (select `tbl_contratos_items_r`.`Mes`, `tbl_contratos_items_r`.`IdItem`, SUM(`tbl_contratos_items_r`.`Cantidad`) as Cantidad, MAX(`tbl_contratos_items_r`.`Monto`) as monto from `tbl_contratos_items_r` GROUP BY `tbl_contratos_items_r`.`Mes`, `tbl_contratos_items_r`.`IdItem`) as tbl_contratos_items_r ON `tbl_contratos_items`.`IdContratoItem` = `tbl_contratos_items_r`.`IdItem` AND `tbl_contratos_items_r`.`Mes` <= ultimomes.mes
        left join tbl_contratos_items_r as rm on tbl_contratos_items.IdContratoItem = rm.IdItem and DATE_FORMAT(rm.mes,'%m-%Y') = DATE_FORMAT(ultimomes.mes,'%m-%Y')
        left join tbl_contratos_items_p as pm on tbl_contratos_items.IdContratoItem = pm.IdItem and DATE_FORMAT(pm.mes,'%m-%Y') = DATE_FORMAT(ultimomes.mes,'%m-%Y')
   ) a
left join tbl_contratos_items b on a.IdParent = b.IdContratoItem and a.contrato_id = b.contrato_id
left join tbl_contratos_items c on b.IdParent = c.IdContratoItem and b.contrato_id = c.contrato_id
left join tbl_contratos_items d on c.IdParent = d.IdContratoItem and c.contrato_id = d.contrato_id
left join tbl_unidades e on a.IdUnidad = e.IdUnidad
) as tbl_contratos_items ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contratos_items.IdContratoItem IS NOT NULL AND tbl_contratos_items.IdParent IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
