<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class anotaciones extends Sximo  {
	
	protected $table = 'tbl_anotaciones';
	protected $primaryKey = 'IdAnotacion';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_anotaciones.* FROM tbl_anotaciones  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_anotaciones.IdAnotacion IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
