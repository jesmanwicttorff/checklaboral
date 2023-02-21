<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblkpi extends Sximo  {
	
	protected $table = 'tbl_kpis';
	protected $primaryKey = 'IdKpi';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  select tbl_kpis.IdKpi, 
				         tbl_kpis.contrato_id,
  					     tbl_kpis.IdTipo,
  					     tbl_kpis.Nombre,
				         tbl_kpis.Descripcion,
				         tbl_kpis.IdUnidad,
				         tbl_kpis.Formula,
				         tbl_kpis.RangoSuperior,
				         tbl_kpis.RangoInferior,
				         CASE WHEN tbl_kpis.IdTipo IN (1, 3)  THEN
			                concat(' >= ', REPLACE(CAST(tbl_kpis.RangoInferior AS DECIMAL(11,2)),'.',','), ' ', tbl_kpis.IdUnidad )
			             WHEN tbl_kpis.IdTipo IN (2, 4) THEN
			                concat(' <= ', REPLACE(CAST(tbl_kpis.RangoInferior AS DECIMAL(11,2)),'.',','), ' ', tbl_kpis.IdUnidad)
			             END as Limite,
				         tbl_kpis.IdEstatus,
				         CASE WHEN tbl_kpis.IdEstatus = 1 THEN 
				                   'Activo'
				              ELSE 
				                   'Inactivo'
				              END as IdEstatusDescripcion,
				         tbl_kpis.entry_by,
				         tbl_kpis.updated_by,
				         tbl_kpis.created_at,
				         tbl_kpis.updated_at,
				         tbl_kpis_tipos.Nombre as Tipo,
				         concat(tbl_contrato.cont_numero , ' - ', tbl_contrato.cont_nombre) as Contrato,
				         concat(tb_users.first_name, ' ', tb_users.last_name) as entry_by_name
				  from tbl_kpis
		          INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_kpis.contrato_id 
		          INNER JOIN tbl_contratistas ON tbl_contratistas.IdContratista = tbl_contrato.IdContratista
		          INNER JOIN tbl_kpis_tipos ON tbl_kpis_tipos.IdTipo = tbl_kpis.IdTipo
		          INNER JOIN tb_users ON tb_users.id = tbl_kpis.entry_by ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_kpis.IdKpi IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
