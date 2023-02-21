<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblcontclasecosto extends Sximo  {
	
	protected $table = 'tbl_contclasecosto';
	protected $primaryKey = 'claseCosto_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contclasecosto.* FROM tbl_contclasecosto  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contclasecosto.claseCosto_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
