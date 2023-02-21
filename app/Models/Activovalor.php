<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class activovalor extends Sximo  {
	
	protected $table = 'tbl_activos_data_detalle';
	protected $primaryKey = 'IdActivoDataDetalle';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_activos_data_detalle.* FROM tbl_activos_data_detalle  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_activos_data_detalle.IdActivoDataDetalle IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
