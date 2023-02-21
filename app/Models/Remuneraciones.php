<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class remuneraciones extends Sximo  {
	
	protected $table = 'tbl_f30_1';
	protected $primaryKey = 'IdF301';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return " SELECT tbl_contratistas.Rut, tbl_contratistas.RazonSocial, tbl_contrato.contrato_id, tbl_contrato.cont_numero,  sum(tbl_f30_1.TrabajadoresVigentes) TrabajadoresVigentes, sum(tbl_f30_1.TotalCotizaciones) as TotalCotizaciones
FROM tbl_f30_1
INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_f30_1.contrato_id
INNER JOIN tbl_contratistas on tbl_contratistas.IdContratista = tbl_f30_1.IdContratista ";
	}	

	public static function queryWhere(  ){
		
		return "  ";
	}
	
	public static function queryGroup(){
		return " group by tbl_contratistas.Rut, tbl_contratistas.RazonSocial, tbl_contrato.contrato_id, tbl_contrato.cont_numero ";
	}
	

}
