<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblequifax extends Sximo  {
	
	protected $table = 'tbl_equifax';
	protected $primaryKey = 'eq_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_equifax.* FROM tbl_equifax  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_equifax.eq_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
