<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblcontratistacondicionfin extends Sximo  {
	
	protected $table = 'tbl_contratistacondicionfin';
	protected $primaryKey = 'finContratista_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contratistacondicionfin.* FROM tbl_contratistacondicionfin  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contratistacondicionfin.finContratista_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
