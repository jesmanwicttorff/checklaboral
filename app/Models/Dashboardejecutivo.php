<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class dashboardejecutivo extends Sximo  {
	
	protected $table = 'tbl_contrato';
	protected $primaryKey = 'contrato_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contrato.* FROM tbl_contrato  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contrato.contrato_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
