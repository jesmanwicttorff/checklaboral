<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class preguntcalificacion extends Sximo  {
	
	protected $table = 'tbl_preguncalificacion';
	protected $primaryKey = 'IdCalificacion';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_preguncalificacion.* FROM tbl_preguncalificacion  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_preguncalificacion.IdCalificacion IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
