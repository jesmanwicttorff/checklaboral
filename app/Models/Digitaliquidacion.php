<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class digitaliquidacion extends Sximo  {
	
	protected $table = 'tbl_liquidaciones_digitadas';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_liquidaciones_digitadas.* FROM tbl_liquidaciones_digitadas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_liquidaciones_digitadas.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
