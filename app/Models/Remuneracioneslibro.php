<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class remuneracioneslibro extends Sximo  {
	
	protected $table = 'tbl_remuneraciones_mensual';
	protected $primaryKey = 'id_remuneraciones_real';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_remuneraciones_mensual.* FROM tbl_remuneraciones_mensual  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_remuneraciones_mensual.id_remuneraciones_real IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
