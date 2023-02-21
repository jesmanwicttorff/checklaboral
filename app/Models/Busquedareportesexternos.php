<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class busquedareportesexternos extends Sximo  {
	
	protected $table = 'tbl_reportes_externos_detalle';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_reportes_externos_detalle.id, tbl_reportes_externos.nombre, tbl_contrato.cont_numero, tbl_reportes_externos_detalle.fecha, tbl_reportes_externos_detalle.comentarios, tbl_reportes_externos_detalle.created_at  
		          FROM tbl_reportes_externos_detalle
		          INNER JOIN dim_tiempo ON dim_tiempo.fecha = tbl_reportes_externos_detalle.fecha
		          INNER JOIN tbl_contrato ON tbl_reportes_externos_detalle.contrato_id = tbl_contrato.contrato_id
		          INNER JOIN tbl_reportes_externos ON tbl_reportes_externos_detalle.idreporteexterno = tbl_reportes_externos.id ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_reportes_externos_detalle.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
