<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblcontratos extends Sximo  {
	
	protected $table = 'tbl_contrato';
	protected $primaryKey = 'contrato_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return " SELECT contrato_id,IdContratista,cont_nombre,cont_rutp_sd,cont_rutp_dv,cont_numero,cont_proveedor,categoria_id,cont_fechaInicio,cont_fechaFin,cont_montoTotal,admin_id,segmento_id,geo_id,cont_compagnia,afuncional_id,claseCosto_id,cont_fechaEstado,cont_glosaDescriptiva,cont_estado,firma_id ,

CASE WHEN firma_id = 1 THEN 'FIRMADO' ELSE 'SIN FIRMAR' END estado_firma ,firma_fecha
FROM tbl_contrato ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contrato.contrato_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
