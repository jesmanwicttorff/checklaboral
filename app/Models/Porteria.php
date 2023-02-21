<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class porteria extends Sximo  {
	
	protected $table = 'tbl_accesos';
	protected $primaryKey = 'IdAcceso';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "    SELECT tbl_accesos.*, tbl_personas.RUT
					FROM tbl_accesos
					LEFT JOIN tbl_personas ON tbl_personas.IdPersona = tbl_accesos.IdPersona  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_accesos.IdAcceso IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
