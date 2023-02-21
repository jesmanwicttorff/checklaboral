<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblmorosidad extends Sximo  {
	
	protected $table = 'tbl_morosidad';
	protected $primaryKey = 'id_mor';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_morosidad.* FROM tbl_morosidad  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_morosidad.id_mor IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
