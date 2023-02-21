<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestasmasterdetalle extends Sximo  {
	
	protected $table = 'tbl_encuestas_master_detalle';
	protected $primaryKey = 'IdEncuestaMasterDetalle';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_encuestas_master_detalle.* FROM tbl_encuestas_master_detalle  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_encuestas_master_detalle.IdEncuestaMasterDetalle IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
