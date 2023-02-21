<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class reportesexternos extends Sximo  {
	
	protected $table = 'tbl_reportes_externos';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_reportes_externos.*, tbl_contsegmento.seg_nombre as segmentos, case when tbl_reportes_externos.idperiodidicad = 1 then 'Diario'
                                                                                                                                          when tbl_reportes_externos.idperiodidicad = 2 then 'Semanal'
                                                                                                                                          when tbl_reportes_externos.idperiodidicad = 3 then 'Mensual'
                                                                                                                                          when tbl_reportes_externos.idperiodidicad = 4 then 'Semestre'
                                                                                                                                          when tbl_reportes_externos.idperiodidicad = 5 then 'Anual' end as Periodicidad, 
                                                                                                                                          case when idtipo = 1 then 'PBI' else 'No especificado' end as Tipo,
                                                                                                                                          case when idestatus = 1 then 'Activo' else 'No activo' end as Estatus
FROM tbl_reportes_externos
INNER JOIN tbl_contsegmento ON tbl_contsegmento.segmento_id = tbl_reportes_externos.idsegmento  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_reportes_externos.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
