<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class activos extends Sximo  {

	protected $table = 'tbl_activos_data';
	protected $primaryKey = 'IdActivoData';

	public function __construct() {
		parent::__construct();

	}

	public static function querySelect(  ){

		return "  SELECT tbl_contrato.contrato_id, tbl_contrato.cont_numero, tbl_contratistas.RUT, tbl_contratistas.RazonSocial
		          FROM tbl_activos_data
		          RIGHT JOIN tbl_contrato ON tbl_activos_data.contrato_id = tbl_contrato.contrato_id
		          INNER JOIN tbl_contratistas ON tbl_contrato.IdContratista = tbl_contratistas.IdContratista ";

	}

	public static function queryWhere(  ){
		return "  WHERE tbl_contrato.contrato_id IS NOT NULL ";

	}

	public static function queryGroup(){
		return " GROUP BY tbl_contrato.contrato_id, tbl_contrato.cont_numero, tbl_contratistas.RUT, tbl_contratistas.RazonSocial ";

	}


}
