<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class visionxpartida extends Sximo  {
	
	protected $table = 'tbl_contrato';
	protected $primaryKey = 'contrato_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return " SELECT tbl_contratistas.RazonSocial, tbl_contrato.contrato_id, tbl_contrato.cont_numero, tbl_contrato.cont_fechaInicio, tbl_contrato.cont_fechaFin, cont_MontoTotal, count(*) as CantidadItems, sum(tbl_contratos_items.Cantidad*tbl_contratos_items.Monto) as MontoTotalItems
FROM tbl_contratos_items
INNER JOIN tbl_contrato ON tbl_contratos_items.contrato_id = tbl_contrato.contrato_id
INNER JOIN tbl_contratistas ON tbl_contrato.IdContratista = tbl_contratistas.IdContratista ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contrato.contrato_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return " GROUP BY tbl_contratistas.RazonSocial, tbl_contrato.contrato_id, tbl_contrato.cont_numero, tbl_contrato.cont_fechaInicio, tbl_contrato.cont_fechaFin ";
	}
	

}
