<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblkpimensual extends Sximo  {
	
	protected $table = 'tbl_kpimensual';
	protected $primaryKey = 'detalle_kpi';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_kpimensual.* FROM tbl_kpimensual  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_kpimensual.detalle_kpi IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
