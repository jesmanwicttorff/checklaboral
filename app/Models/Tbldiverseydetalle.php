<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tbldiverseydetalle extends Sximo  {
	
	protected $table = 'tbl_diversey_itemizado';
	protected $primaryKey = 'itemd_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_diversey_itemizado.* FROM tbl_diversey_itemizado  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_diversey_itemizado.itemd_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
