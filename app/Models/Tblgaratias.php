<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblgaratias extends Sximo  {
	
	protected $table = 'tbl_garantias';
	protected $primaryKey = 'gar_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_garantias.* FROM tbl_garantias  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_garantias.gar_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
