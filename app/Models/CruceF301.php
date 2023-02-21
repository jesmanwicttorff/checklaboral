<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class CruceF301 extends Sximo  {
	
	protected $table = 'tbl_cruce_f30_1';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_cruce_f30_1.* FROM tbl_cruce_f30_1  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_cruce_f30_1.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
