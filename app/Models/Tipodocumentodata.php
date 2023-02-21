<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tipodocumentodata extends Sximo  {
	
	protected $table = 'tbl_tipo_documento_data';
	protected $primaryKey = 'IdTipoDocumentoData';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_tipo_documento_data.* FROM tbl_tipo_documento_data  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tipo_documento_data.IdTipoDocumentoData IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
