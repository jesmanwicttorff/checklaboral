<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class moduloalertastdocumento extends Sximo  {
	
	protected $table = 'tbl_tipos_documentos';
	protected $primaryKey = 'IdTipoDocumento';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_tipos_documentos.* FROM tbl_tipos_documentos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tipos_documentos.IdTipoDocumento IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
