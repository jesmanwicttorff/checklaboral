<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class accesoareas extends Sximo  {
	
	protected $table = 'tbl_acceso_areas';
	protected $primaryKey = 'IdAccesoArea';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_acceso_areas.* FROM tbl_acceso_areas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_acceso_areas.IdAccesoArea IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
