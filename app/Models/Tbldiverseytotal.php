<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tbldiverseytotal extends Sximo  {
	
	protected $table = 'tbl_diversey_totales';
	protected $primaryKey = 'globaldiversey_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_diversey_totales.* FROM tbl_diversey_totales  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_diversey_totales.globaldiversey_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
