<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class historialactivos extends Sximo  {
	
	protected $table = 'tbl_historial_acreditacion_activos';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_historial_acreditacion_activos.* FROM tbl_historial_acreditacion_activos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_historial_acreditacion_activos.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
