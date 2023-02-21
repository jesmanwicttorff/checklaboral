<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class cargaf extends Sximo  {
	
	protected $table = 'tbl_carga_documento';
	protected $primaryKey = 'IdCargaDocumento';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_carga_documento.* 
FROM tbl_carga_documento ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_carga_documento.IdCargaDocumento IS NOT NULL
AND tbl_carga_documento.IdTipoDocumento = 1 -- Filtra archivos F30-1 ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
