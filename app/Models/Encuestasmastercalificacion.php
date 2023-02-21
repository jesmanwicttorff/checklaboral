<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestasmastercalificacion extends Sximo  {
	
	protected $table = 'tbl_encuestas_master_calificacion';
	protected $primaryKey = 'IdEncuestaMasterCalificacion';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_encuestas_master_calificacion.* FROM tbl_encuestas_master_calificacion  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_encuestas_master_calificacion.IdEncuestaMasterCalificacion IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
