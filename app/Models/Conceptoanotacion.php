<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class conceptoanotacion extends Sximo  {
	
	protected $table = 'tbl_concepto_anotacion';
	protected $primaryKey = 'IdConceptoAnotacion';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_concepto_anotacion.* FROM tbl_concepto_anotacion  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_concepto_anotacion.IdConceptoAnotacion IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
