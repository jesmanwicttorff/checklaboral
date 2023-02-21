<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class centros extends Sximo  {
	
	protected $table = 'tbl_centro';
	protected $primaryKey = 'IdCentro';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_centro.* FROM tbl_centro  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_centro.IdCentro IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
