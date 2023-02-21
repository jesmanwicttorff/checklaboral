<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestasdetalle extends Sximo  {
	
	protected $table = 'tbl_encuestas_detalle';
	protected $primaryKey = 'IdEncuestaDetalle';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_encuestas_detalle.* FROM tbl_encuestas_detalle  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_encuestas_detalle.IdEncuestaDetalle IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
