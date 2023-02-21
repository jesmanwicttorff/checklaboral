<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class requisitosdetalleroles extends Sximo  {
	
	protected $table = 'tbl_requisitos_detalles';
	protected $primaryKey = 'IdRequisitoDetalle';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_requisitos_detalles.* FROM tbl_requisitos_detalles  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_requisitos_detalles.IdRequisitoDetalle IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
