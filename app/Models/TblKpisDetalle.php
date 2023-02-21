<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblkpisdetalle extends Sximo  {
	
	protected $table = 'tbl_kpis_detalles';
	protected $primaryKey = 'IdKpiDetalle';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_kpis_detalles.IdKpiDetalle,
				         tbl_kpis_detalles.IdKpi,
				         tbl_kpis_detalles.Fecha,
				         REPLACE(CAST(tbl_kpis_detalles.Puntaje AS DECIMAL(11,2)),'.',',') as Puntaje,
				         tbl_kpis_detalles.Resultado,
				         tbl_kpis_detalles.RangoSuperior,
				         tbl_kpis_detalles.RangoInferior,
				         tbl_kpis_detalles.MetaSuperior,
				         tbl_kpis_detalles.MetaInferior,
				         tbl_kpis_detalles.entry_by,
				         tbl_kpis_detalles.updated_by,
				         tbl_kpis_detalles.created_at,
				         tbl_kpis_detalles.updated_at,
		                 tbl_contratistas.rut,
		                 tbl_contratistas.RazonSocial,
		                 CASE WHEN tbl_kpis_detalles.Puntaje IS NOT NULL THEN
	                            CASE WHEN tbl_kpis.IdTipo = 1 THEN
					                  concat(' >= ', REPLACE(CAST(tbl_kpis_detalles.RangoSuperior AS DECIMAL(11,2)),'.',','), ' ', tbl_kpis.IdUnidad)
					                WHEN tbl_kpis.IdTipo = 2 THEN
					                  concat(' <= ', REPLACE(CAST(tbl_kpis_detalles.RangoSuperior AS DECIMAL(11,2)),'.',','), ' ', tbl_kpis.IdUnidad)
					                WHEN tbl_kpis.IdTipo = 3 THEN
					                  CASE WHEN tbl_kpis_detalles.MetaInferior = tbl_kpis_detalles.MetaSuperior THEN
					                    concat(' [ ', REPLACE(CAST(tbl_kpis_detalles.MetaInferior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' ] ' )
					                  ELSE
					                  	concat(' [ ', REPLACE(CAST(tbl_kpis_detalles.MetaInferior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' - ', REPLACE(CAST(tbl_kpis_detalles.MetaSuperior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' ] ' )
					                  END
					                WHEN tbl_kpis.IdTipo = 4 THEN
					                  CASE WHEN tbl_kpis_detalles.MetaInferior = tbl_kpis_detalles.MetaSuperior THEN
					                    concat(' [ ', REPLACE(CAST(tbl_kpis_detalles.MetaInferior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' ] ' )
					                  ELSE
					                    concat(' [ ', REPLACE(CAST(tbl_kpis_detalles.MetaSuperior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' - ', REPLACE(CAST(tbl_kpis_detalles.MetaInferior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' ] ' )
					                  END
					            END
			             ELSE
			               ''   
			             END as Limite,
						 CASE WHEN tbl_kpis_detalles.Resultado > 100 THEN 100
			                   WHEN tbl_kpis_detalles.Resultado < 0 THEN 0 ELSE tbl_kpis_detalles.Resultado END as  ResultadoAjustado,
		                 tbl_contrato.cont_numero,
		                 tbl_contrato.cont_nombre,
		                 concat(tbl_contrato.cont_numero, ' - ', tbl_contratistas.RazonSocial, ' - ', tbl_contratistas.rut) as contrato_info,
						 tbl_kpis.contrato_id,
						 tbl_kpis.Nombre,
		                 tbl_kpis.Descripcion,
		                 tbl_kpis.IdTipo,
		                 tbl_kpis_tipos.Nombre as Tipo,
		                 tbl_kpis.RangoInferior,
		                 tbl_kpis.IdUnidad,
		                 tbl_kpis.Formula,
		                 concat(tb_users.first_name, ' ', tb_users.last_name) as entry_by_name
		          FROM tbl_kpis_detalles
		          INNER JOIN tbl_kpis ON tbl_kpis.IdKpi = tbl_kpis_detalles.IdKpi
		          INNER JOIN tbl_kpis_tipos ON tbl_kpis_tipos.IdTipo = tbl_kpis.IdTipo
		          INNER JOIN tb_users ON tb_users.id = tbl_kpis_detalles.entry_by
		          INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_kpis.contrato_id 
		          INNER JOIN tbl_contratistas ON tbl_contratistas.IdContratista = tbl_contrato.IdContratista ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_kpis_detalles.IdKpiDetalle IS NOT NULL AND tbl_kpis.IdEstatus = 1 ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
