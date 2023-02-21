<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class contratoscentros extends Sximo  {
	
	protected $table = 'tbl_contratos_centros';
	protected $primaryKey = 'IdContratosCentros';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contratos_centros.*, tbl_centro.Descripcion as Centro
				  FROM tbl_contratos_centros
				  INNER JOIN tbl_centro ON  tbl_centro.IdCentro = tbl_contratos_centros.IdCentro ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contratos_centros.IdContratosCentros IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
