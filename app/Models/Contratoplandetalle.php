<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class contratoplandetalle extends Sximo  {
	
	protected $table = 'tbl_contratos_plan';
	protected $primaryKey = 'IdContratoPlan';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contratos_plan.* FROM tbl_contratos_plan  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contratos_plan.IdContratoPlan IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
