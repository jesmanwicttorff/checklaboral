<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class cargadocumento extends Sximo  {
	
	protected $table = 'tbl_carga_documento';
	protected $primaryKey = 'IdCargaDocumento';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_carga_documento.* FROM tbl_carga_documento  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_carga_documento.IdCargaDocumento IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
