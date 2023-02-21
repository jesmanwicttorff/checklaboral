<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class agrupaciongeografica extends Sximo  {
	
	protected $table = 'tbl_agrupacion_geografica';
	protected $primaryKey = 'IdAgrupacion';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_agrupacion_geografica.* FROM tbl_agrupacion_geografica  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_agrupacion_geografica.IdAgrupacion IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
