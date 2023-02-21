<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblevalcontratista extends Sximo  {
	
	protected $table = 'tbl_evalcontratista';
	protected $primaryKey = 'evalcont_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_evalcontratista.* FROM tbl_evalcontratista  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_evalcontratista.evalcont_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
