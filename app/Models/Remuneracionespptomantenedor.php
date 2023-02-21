<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class remuneracionespptomantenedor extends Sximo  {
	
	protected $table = 'tbl_contrato';
	protected $primaryKey = 'contrato_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return " SELECT tbl_contrato.contrato_id, tbl_contrato.cont_proveedor, tbl_documentos.IdDocumento, tbl_tipos_documentos.IdTipoDocumento, tbl_documentos.IdEstatus FROM tbl_documentos
LEFT JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_documentos.contrato_id
LEFT JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contrato.contrato_id IS NOT NULL
 AND tbl_documentos.IdTipoDocumento = 11 ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
