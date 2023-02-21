<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblcontareafuncional extends Sximo  {
	
	protected $table = 'tbl_contareafuncional';
	protected $primaryKey = 'afuncional_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contareafuncional.* FROM tbl_contareafuncional  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contareafuncional.afuncional_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
