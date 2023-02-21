<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class activosdetalle extends Sximo  {
	
	protected $table = 'tbl_activos_detalle';
	protected $primaryKey = 'IdActivoDetalle';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_activos_detalle.* FROM tbl_activos_detalle  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_activos_detalle.IdActivoDetalle IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
