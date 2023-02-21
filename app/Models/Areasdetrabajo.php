<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class areasdetrabajo extends Sximo  {
	
	protected $table = 'tbl_area_de_trabajo';
	protected $primaryKey = 'IdAreaTrabajo';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_area_de_trabajo.* FROM tbl_area_de_trabajo  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_area_de_trabajo.IdAreaTrabajo IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
