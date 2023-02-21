<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class asigareasctrl extends Sximo  {
	
	protected $table = 'tbl_asignacion';
	protected $primaryKey = 'id_Asig';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_asignacion.* FROM tbl_asignacion  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_asignacion.id_Asig IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
