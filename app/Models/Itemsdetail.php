<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class itemsdetail extends Sximo  {

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
       a.Descripcion,
			 a.IdUnidad as Unidad,
			  a.monto as Precio,
       a.cantidad,
      a.contrato_id
from tbl_contratos_items a
left join tbl_contratos_items b on a.IdParent = b.IdContratoItem and a.contrato_id = b.contrato_id
left join tbl_contratos_items c on b.IdParent = c.IdContratoItem and b.contrato_id = c.contrato_id
left join tbl_contratos_items d on c.IdParent = d.IdContratoItem and c.contrato_id = d.contrato_id) as tbl_contratos_items  ";
	}

	public static function queryWhere(  ){

		return "  WHERE tbl_contratos_items.IdContratoItem IS NOT NULL AND tbl_contratos_items.IdParent IS NOT NULL ";
	}

	public static function queryGroup(){
		return "  ";
	}


}
