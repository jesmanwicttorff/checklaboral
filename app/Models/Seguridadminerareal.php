<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class seguridadminerareal extends Sximo  {
	
	protected $table = 'tbl_e200_real';
	protected $primaryKey = 'id_e200_real';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contrato.cont_proveedor, tbl_e200_real.* FROM tbl_e200_real 
INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_e200_real.contrato_id ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_e200_real.id_e200_real IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
