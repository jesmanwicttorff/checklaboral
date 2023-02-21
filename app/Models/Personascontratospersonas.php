<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class personascontratospersonas extends Sximo  {
	
	protected $table = 'tbl_contratos_personas';
	protected $primaryKey = 'IdContratosPersonas';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contratos_personas.IdPersona, tbl_contratos_personas.contrato_id, tbl_personas.RUT, tbl_personas.Nombres, tbl_personas.Apellidos, tbl_roles.descripción AS Roles
FROM tbl_contratos_personas
INNER JOIN tbl_personas ON tbl_contratos_personas.IdPersona = tbl_personas.IdPersona
INNER JOIN tbl_roles ON tbl_contratos_personas.IdRol = tbl_roles.IdRol  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contratos_personas.IdContratosPersonas IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
