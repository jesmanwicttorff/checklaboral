<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class regiones extends Sximo  {
	
	protected $table = 'tbl_region';
	protected $primaryKey = 'IdRegion';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_region.* FROM tbl_region  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_region.IdRegion IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
