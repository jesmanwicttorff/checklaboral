<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class accesoslog extends Sximo  {

	protected $table = 'tbl_accesos_log';
	protected $primaryKey = 'IdAccesoLog';

	public function __construct() {
		parent::__construct();

	}

	public static function querySelect(  ){

        $lstrResultado = "";
        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');
        
        $lstrResultado = "  SELECT * 
                            FROM (SELECT tbl_accesos_log.*,
                                         CASE WHEN tbl_accesos_log.IdTipoAcceso = 1 THEN 'ENTRADA' ELSE 'SALIDA' END AS Acceso ,
                                         CASE WHEN IdTipoEntidad = 1 THEN 'PERSONA' ELSE 'ACTIVO' END AS Tipo,
                                         tbl_activos.Descripcion AS Activo,
                                         CONCAT(tbl_activos_detalle.Etiqueta, ': ', tbl_activos_data_detalle.Valor) AS Ident,
                                         tbl_centro.Descripcion AS Centro,
                                         tbl_area_de_trabajo.Descripcion AS area_trabajo,tbl_contrato.cont_numero,tbl_contratistas.`RUT`,tbl_contratistas.`RazonSocial`
                                  FROM tbl_accesos_log
                                  INNER JOIN tbl_area_de_trabajo ON tbl_accesos_log.IdAreaTrabajo = tbl_area_de_trabajo.IdAreaTrabajo
                                  INNER JOIN tbl_centro ON tbl_area_de_trabajo.IdCentro=tbl_centro.IdCentro
                                  INNER JOIN tbl_activos ON tbl_accesos_log.IdTipoSubEntidad=tbl_activos.IdActivo
                                  INNER JOIN tbl_activos_data ON tbl_activos.IdActivo=tbl_activos_data.IdActivo AND tbl_accesos_log.IdEntidad=tbl_activos_data.IdActivoData
                                  INNER JOIN tbl_contrato ON tbl_activos_data.contrato_id= tbl_contrato.contrato_id
                                  INNER JOIN tbl_contratistas ON tbl_contrato.IdContratista=tbl_contratistas.IdContratista
                                  INNER JOIN tbl_activos_detalle ON tbl_activos.IdActivo=tbl_activos_detalle.IdActivo
                                  INNER JOIN tbl_activos_data_detalle ON tbl_activos_detalle.IdActivoDetalle=tbl_activos_data_detalle.IdActivoDetalle AND tbl_activos_data.IdActivoData=tbl_activos_data_detalle.IdActivoData
                                  WHERE IdTipoEntidad = 2 
                                  AND tbl_activos_detalle.Unico='SI' ";
        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
        $lstrResultado .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';

        $lstrResultado .= "UNION ALL
        SELECT  tbl_accesos_log.*,
        CASE WHEN tbl_accesos_log.IdTipoAcceso = 1 THEN 'ENTRADA' ELSE 'SALIDA' END AS Acceso ,
        CASE WHEN IdTipoEntidad = 1 THEN 'PERSONA' ELSE 'ACTIVO' END AS Tipo,
        CASE WHEN IdTipoSubEntidad = 0 THEN '--' ELSE '--' END AS Activo,
        CONCAT(tbl_personas.Rut, ' ', tbl_personas.Nombres, ' ', tbl_personas.Apellidos) AS Ident,
        tbl_centro.Descripcion AS Centro,
        tbl_area_de_trabajo.Descripcion AS area_trabajo,
        tbl_contrato.cont_numero,
        tbl_contratistas.RUT,
        tbl_contratistas.RazonSocial
        FROM tbl_accesos_log
        INNER JOIN tbl_area_de_trabajo ON tbl_accesos_log.IdAreaTrabajo = tbl_area_de_trabajo.IdAreaTrabajo
        INNER JOIN tbl_centro ON tbl_area_de_trabajo.IdCentro=tbl_centro.IdCentro
        INNER JOIN tbl_personas ON tbl_accesos_log.IdEntidad=tbl_personas.IdPersona
        LEFT JOIN tbl_contratos_personas ON tbl_personas.IdPersona= tbl_contratos_personas.IdPersona
        LEFT JOIN tbl_contrato ON tbl_contratos_personas.contrato_id= tbl_contrato.contrato_id
        LEFT JOIN tbl_contratistas ON tbl_contrato.IdContratista=tbl_contratistas.IdContratista
        WHERE IdTipoEntidad=1 ";

        $lstrResultado .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';

        $lstrResultado .= "UNION ALL 
        SELECT  tbl_accesos_log.*,
        CASE WHEN tbl_accesos_log.IdTipoAcceso = 1 THEN 'ENTRADA' ELSE 'SALIDA' END AS Acceso ,
        CASE WHEN IdTipoEntidad = 1 THEN 'PERSONA' ELSE 'ACTIVO' END AS Tipo,
        CASE WHEN IdTipoSubEntidad = 0 THEN '--' ELSE '--' END AS Activo,
        CONCAT(tbl_accesos_log.data_rut, ' - ', tbl_accesos_log.data_nombres, ' ', tbl_accesos_log.data_apellidos) AS Ident,
        tbl_centro.Descripcion AS Centro,
        tbl_area_de_trabajo.Descripcion AS area_trabajo,
        '' as cont_numero,
        '' as RUT,
        '' as RazonSocial
        FROM tbl_accesos_log
        INNER JOIN tbl_area_de_trabajo ON tbl_accesos_log.IdAreaTrabajo = tbl_area_de_trabajo.IdAreaTrabajo
        INNER JOIN tbl_centro ON tbl_area_de_trabajo.IdCentro=tbl_centro.IdCentro
        WHERE IdTipoEntidad=1) acce ";

        return $lstrResultado;
	}


	public static function queryWhere(  ){

            return "  ";
	}

	public static function queryGroup(){
		return "  ";
	}


}
