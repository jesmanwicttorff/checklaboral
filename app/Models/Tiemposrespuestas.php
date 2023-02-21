<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tiemposrespuestas extends Sximo  {
	
	protected $table = 'lod_sla_plan';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT lod_sla_plan.* FROM lod_sla_plan  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE lod_sla_plan.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
