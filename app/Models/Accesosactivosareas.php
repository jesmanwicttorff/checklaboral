<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class accesosactivosareas extends Sximo  {
	
	protected $table = 'tbl_acceso_activos_areas';
	protected $primaryKey = 'IdAccesoActivoArea';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_acceso_activos_areas.* FROM tbl_acceso_activos_areas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_acceso_activos_areas.IdAccesoActivoArea IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
