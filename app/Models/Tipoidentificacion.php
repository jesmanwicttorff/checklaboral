<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tipoidentificacion extends Sximo  {
	
	protected $table = 'tbl_tipos_identificacion';
	protected $primaryKey = 'IdTipoIdentificacion';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_tipos_identificacion.* FROM tbl_tipos_identificacion  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tipos_identificacion.IdTipoIdentificacion IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
