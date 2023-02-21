<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class remuneracionpersonal extends Sximo  {
	
	protected $table = 'tbl_contrato';
	protected $primaryKey = 'contrato_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return " SELECT DISTINCT a.contrato_id, a.cont_numero, a.cont_proveedor, 
SUM(CASE WHEN b.contrato_id IS NULL THEN 0 ELSE 1 END) as e200_mensual,
SUM(CASE WHEN c.contrato_id IS NULL THEN 0 ELSE 1 END) as remuneracion_mensual,
SUM(CASE WHEN g.Descripcion LIKE '%e200%' THEN 1 ELSE 0 END) as e200_ppto,
SUM(CASE WHEN g.Descripcion LIKE '%remuneracion%' THEN 1 ELSE 0 END) as remuneracion_ppto
FROM tbl_contrato as a
LEFT JOIN tbl_e200_mensual as b ON a.contrato_id = b.contrato_id
LEFT JOIN tbl_remuneraciones_mensual as c ON a.contrato_id = c.contrato_id
LEFT JOIN tbl_documentos as d ON a.contrato_id = d.contrato_id
LEFT JOIN tbl_documento_valor as e ON d.IdDocumento = e.IdDocumento
LEFT JOIN tbl_tipo_documento_valor as f ON e.IdTipoDocumentoValor = f.IdTipoDocumentoValor
LEFT JOIN tbl_tipos_documentos as g ON f.IdTipoDocumento = g.IdTipoDocumento ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE a.contrato_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
