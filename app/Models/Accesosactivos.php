<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class accesosactivos extends Sximo  {
	
	protected $table = 'tbl_accesos_activos';
	protected $primaryKey = 'IdAccesoActivo';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_accesos_activos.* FROM tbl_accesos_activos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_accesos_activos.IdAccesoActivo IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
