<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class indicadores extends Sximo  {
	
	protected $table = 'tbl_indicadores';
	protected $primaryKey = 'IdIndicador';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_indicadores.* FROM tbl_indicadores  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_indicadores.IdIndicador IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
