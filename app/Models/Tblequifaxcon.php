<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblequifaxcon extends Sximo  {
	
	protected $table = 'tbl_equifax_consolidado';
	protected $primaryKey = 'IdEquifaxConsolidado';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_equifax_consolidado.* FROM tbl_equifax_consolidado  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_equifax_consolidado.IdEquifaxConsolidado IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
