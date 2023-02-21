<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class justificacionproveedorunicos extends Sximo  {
	
	protected $table = 'justificacion_proveedor_unicos';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT justificacion_proveedor_unicos.* FROM justificacion_proveedor_unicos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE justificacion_proveedor_unicos.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
