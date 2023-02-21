<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class seguridadminerapptodetalle extends Sximo  {
	
	protected $table = 'tbl_e200_mensual';
	protected $primaryKey = 'id_e200_mensual';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_e200_mensual.* FROM tbl_e200_mensual  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_e200_mensual.id_e200_mensual IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
