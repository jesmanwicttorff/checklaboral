<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblgastoplan extends Sximo  {
	
	protected $table = 'tbl_financiero_and';
	protected $primaryKey = 'financieroand_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_financiero_and.* FROM tbl_financiero_and  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_financiero_and.financieroand_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
