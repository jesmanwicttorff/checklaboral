<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblcalidadadmcont extends Sximo  {
	
	protected $table = 'tbl_calidad_adm_cont';
	protected $primaryKey = 'cal_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_calidad_adm_cont.* FROM tbl_calidad_adm_cont  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_calidad_adm_cont.cal_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
