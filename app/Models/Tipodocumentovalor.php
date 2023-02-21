<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tipodocumentovalor extends Sximo  {
	
	protected $table = 'tbl_tipo_documento_valor';
	protected $primaryKey = 'IdTipoDocumentoValor';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_tipo_documento_valor.* FROM tbl_tipo_documento_valor  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tipo_documento_valor.IdTipoDocumentoValor IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
