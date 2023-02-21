<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblaccidentes extends Sximo  {
	
	protected $table = 'tbl_accidentes';
	protected $primaryKey = 'acc_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_accidentes.* FROM tbl_accidentes  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_accidentes.acc_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
