<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class gruposespecificos extends Sximo  {
	
	protected $table = 'tbl_contratos_grupos_especificos';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contratos_grupos_especificos.* FROM tbl_contratos_grupos_especificos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contratos_grupos_especificos.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
