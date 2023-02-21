<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class noconformidadestemporal extends Sximo  {
	
	protected $table = 'tbl_nc_temp';
	protected $primaryKey = 'idtbl_nc_temp';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_nc_temp.* FROM tbl_nc_temp  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_nc_temp.idtbl_nc_temp IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
