<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class dotacionpptomantenedor extends Sximo  {
	
	protected $table = 'tbl_contrato';
	protected $primaryKey = 'contrato_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return " SELECT 
    tbl_contrato.contrato_id,
    tbl_contrato.cont_proveedor,
    tbl_documentos.IdDocumento,
    tbl_documentos.IdTipoDocumento,
    tbl_documentos.IdEstatus
FROM
    tbl_contrato
        LEFT JOIN
    tbl_documentos ON tbl_contrato.contrato_id = tbl_documentos.contrato_id
        LEFT JOIN
    tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento ";
	}	

	public static function queryWhere(  ){
		
		return " WHERE
    tbl_documentos.IdTipoDocumento = 12 ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
