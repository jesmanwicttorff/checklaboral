<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class activosdetallelistas extends Sximo  {
	
	protected $table = 'tbl_activos_detalles_listas';
	protected $primaryKey = 'IdActivoDetalleLista';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_activos_detalles_listas.* FROM tbl_activos_detalles_listas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_activos_detalles_listas.IdActivoDetalleLista IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
