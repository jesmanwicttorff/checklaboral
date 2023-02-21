<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class reportesexternosdetalle extends Sximo  {
	
	protected $table = 'tbl_reportes_externos_detalle';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_reportes_externos_detalle.*, tbl_reportes_externos.nombre, concat(tbl_contrato.cont_numero,' ',tbl_contrato.cont_nombre) as cont_numero
		          FROM tbl_reportes_externos_detalle
		          INNER JOIN tbl_contrato on tbl_contrato.contrato_id =  tbl_reportes_externos_detalle.contrato_id
		          INNER JOIN tbl_reportes_externos on tbl_reportes_externos_detalle.idreporteexterno = tbl_reportes_externos.id ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_reportes_externos_detalle.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
