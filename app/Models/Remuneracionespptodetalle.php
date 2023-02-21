<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class remuneracionespptodetalle extends Sximo  {
	
	protected $table = 'tbl_remuneraciones_ppto';
	protected $primaryKey = 'id_remuneracion_ppto';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_remuneraciones_ppto.* FROM tbl_remuneraciones_ppto  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_remuneraciones_ppto.id_remuneracion_ppto IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
