<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblfiscalizaciondetalle extends Sximo  {
	
	protected $table = 'tbl_fiscalizacion_detalle';
	protected $primaryKey = 'fiscalizadetalle_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_fiscalizacion_detalle.* FROM tbl_fiscalizacion_detalle  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_fiscalizacion_detalle.fiscalizadetalle_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
