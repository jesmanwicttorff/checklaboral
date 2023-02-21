<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class buscadordocumental extends Sximo  {
	
	protected $table = 'tbl_documentos';
	protected $primaryKey = 'IdDocumento';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
            $sql = " SELECT `tbl_documentos`.*,
       `tbl_tipos_documentos`.`Descripcion` as TipoDocumento,
       `tbl_documentos_estatus`.`Descripcion` as Estatus,
       `tbl_entidades`.`Entidad` as EntidadDescripcion,
       CONCAT(`tbl_contratistas`.`RUT`,' ',`tbl_contratistas`.`RazonSocial`) as Detalle
FROM `tbl_documentos`
INNER JOIN `tbl_contratistas` ON `tbl_documentos`.`IdEntidad` = `tbl_contratistas`.`IdContratista`
INNER JOIN `tbl_tipos_documentos` ON `tbl_tipos_documentos`.`IdTipoDocumento` = `tbl_documentos`.`IdTipoDocumento`
INNER JOIN `tbl_documentos_estatus` ON `tbl_documentos_estatus`.`IdEstatus` = `tbl_documentos`.`IdEstatus`
INNER JOIN `tbl_entidades` ON `tbl_entidades`.`IdEntidad` = `tbl_documentos`.`Entidad`
WHERE `tbl_documentos`.`Entidad` = 1
AND `tbl_documentos`.`IdEstatus` = 5
UNION ALL
SELECT `tbl_documentos`.*,
	   `tbl_tipos_documentos`.`Descripcion` as TipoDocumento,
       `tbl_documentos_estatus`.`Descripcion` as Estatus,
       `tbl_entidades`.`Entidad` as EntidadDescripcion,
       CONCAT(`tbl_contrato`.`cont_proveedor`,' ',`tbl_contrato`.`cont_numero`) as Detalle
FROM `tbl_documentos`
INNER JOIN `tbl_contrato` ON `tbl_documentos`.`IdEntidad` = `tbl_contrato`.`contrato_id`
INNER JOIN `tbl_tipos_documentos` ON `tbl_tipos_documentos`.`IdTipoDocumento` = `tbl_documentos`.`IdTipoDocumento`
INNER JOIN `tbl_documentos_estatus` ON `tbl_documentos_estatus`.`IdEstatus` = `tbl_documentos`.`IdEstatus`
INNER JOIN `tbl_entidades` ON `tbl_entidades`.`IdEntidad` = `tbl_documentos`.`Entidad`
WHERE `tbl_documentos`.`Entidad` = 2
AND `tbl_documentos`.`IdEstatus` = 5
UNION ALL
SELECT `tbl_documentos`.*,
	   `tbl_tipos_documentos`.`Descripcion` as TipoDocumento,
       `tbl_documentos_estatus`.`Descripcion` as Estatus,
       `tbl_entidades`.`Entidad` as EntidadDescripcion,
       CONCAT(`tbl_personas`.`RUT`,' ', `tbl_personas`.`Nombres`,' ', `tbl_personas`.`Apellidos`) as Detalle
FROM `tbl_documentos`
INNER JOIN `tbl_personas` ON `tbl_documentos`.`Entidad` = 3 AND `tbl_documentos`.`IdEntidad` = `tbl_personas`.`IdPersona` 
INNER JOIN `tbl_tipos_documentos` ON `tbl_tipos_documentos`.`IdTipoDocumento` = `tbl_documentos`.`IdTipoDocumento`
INNER JOIN `tbl_documentos_estatus` ON `tbl_documentos_estatus`.`IdEstatus` = `tbl_documentos`.`IdEstatus`
INNER JOIN `tbl_entidades` ON `tbl_entidades`.`IdEntidad` = `tbl_documentos`.`Entidad`
WHERE `tbl_documentos`.`Entidad` = 3
AND `tbl_documentos`.`IdEstatus` = 5
UNION ALL
SELECT `tbl_documentos`.*,
	   `tbl_tipos_documentos`.`Descripcion` as TipoDocumento,
       `tbl_documentos_estatus`.`Descripcion` as Estatus,
       `tbl_entidades`.`Entidad` as EntidadDescripcion,
       tbl_centro.descripcion as Detalle
FROM `tbl_documentos`
INNER JOIN  `tbl_centro` ON `tbl_documentos`.`IdEntidad` = `tbl_centro`.`IdCentro`
INNER JOIN `tbl_tipos_documentos` ON `tbl_tipos_documentos`.`IdTipoDocumento` = `tbl_documentos`.`IdTipoDocumento`
INNER JOIN `tbl_documentos_estatus` ON `tbl_documentos_estatus`.`IdEstatus` = `tbl_documentos`.`IdEstatus`
INNER JOIN `tbl_entidades` ON `tbl_entidades`.`IdEntidad` = `tbl_documentos`.`Entidad`
WHERE `tbl_documentos`.`Entidad` = 6
AND `tbl_documentos`.`IdEstatus` = 5
UNION ALL
SELECT `tbl_documentos`.*,
	   `tbl_tipos_documentos`.`Descripcion` as TipoDocumento,
       `tbl_documentos_estatus`.`Descripcion` as Estatus,
       `tbl_entidades`.`Entidad` as EntidadDescripcion,
       CONCAT(`vw_lista_activos`.`Activo`,' ',`vw_lista_activos`.`Valor`) as Detalle
FROM `tbl_documentos`
INNER JOIN `tbl_tipos_documentos` ON `tbl_tipos_documentos`.`IdTipoDocumento` = `tbl_documentos`.`IdTipoDocumento`
INNER JOIN  `vw_lista_activos` ON `tbl_documentos`.`Entidad` = `vw_lista_activos`.`IdActivo` AND `tbl_documentos`.`IdEntidad` = `vw_lista_activos`.`IdActivoData`
INNER JOIN `tbl_documentos_estatus` ON `tbl_documentos_estatus`.`IdEstatus` = `tbl_documentos`.`IdEstatus`
INNER JOIN `tbl_entidades` ON `tbl_entidades`.`IdEntidad` = `tbl_documentos`.`Entidad`
WHERE `tbl_documentos`.`Entidad` >= 10
AND `tbl_documentos`.`IdEstatus` = 5 ";
            $sql = "SELECT 	a.IdDocumento, 
		a.IdDocumentoRelacion, 
        a.IdRequisito,
        a.IdTipoDocumento,
        a.Entidad,
        a.IdEntidad,
        a.Documento,
        a.DocumentoURL,
        a.DocumentoTexto,
        a.FechaVencimiento,
        a.IdEstatus,
        a.IdEstatusDocumento,
        a.createdOn,
        a.entry_by,
        a.entry_by_access,
        a.updatedOn,
        a.FechaEmision,
        a.Resultado,
        a.contrato_id,
        a.estado_carga,
        `tbl_tipos_documentos`.Descripcion as TipoDocumento,
        `tbl_documentos_estatus`.Descripcion as Estatus,
        `tbl_entidades`.`Entidad` as EntidadDescripcion,
        a.Detalle
	FROM (
SELECT `tbl_documentos`.*,
       CONCAT(`tbl_contratistas`.`RUT`,' ',`tbl_contratistas`.`RazonSocial`) as Detalle
FROM `tbl_documentos`
	INNER JOIN `tbl_contratistas` ON `tbl_documentos`.`IdEntidad` = `tbl_contratistas`.`IdContratista`
WHERE `tbl_documentos`.`Entidad` = 1
	AND `tbl_documentos`.`IdEstatus` = 5
UNION ALL
SELECT `tbl_documentos`.*,
       CONCAT(`tbl_contrato`.`cont_proveedor`,' ',`tbl_contrato`.`cont_numero`) as Detalle
FROM `tbl_documentos`
	INNER JOIN `tbl_contrato` ON `tbl_documentos`.`IdEntidad` = `tbl_contrato`.`contrato_id`
WHERE `tbl_documentos`.`Entidad` = 2
	AND `tbl_documentos`.`IdEstatus` = 5
UNION ALL
SELECT `tbl_documentos`.*,
       CONCAT(`tbl_personas`.`RUT`,' ', `tbl_personas`.`Nombres`,' ', `tbl_personas`.`Apellidos`) as Detalle
FROM `tbl_documentos`
	INNER JOIN `tbl_personas` ON `tbl_documentos`.`Entidad` = 3 AND `tbl_documentos`.`IdEntidad` = `tbl_personas`.`IdPersona` 
WHERE `tbl_documentos`.`Entidad` = 3
	AND `tbl_documentos`.`IdEstatus` = 5
UNION ALL
SELECT `tbl_documentos`.*,
       tbl_centro.descripcion as Detalle
FROM `tbl_documentos`
	INNER JOIN  `tbl_centro` ON `tbl_documentos`.`IdEntidad` = `tbl_centro`.`IdCentro`
WHERE `tbl_documentos`.`Entidad` = 6
	AND `tbl_documentos`.`IdEstatus` = 5
UNION ALL
SELECT `tbl_documentos`.*,
	   CONCAT(`vw_lista_activos`.`Activo`,' ',`vw_lista_activos`.`Valor`) as Detalle
FROM `tbl_documentos`
	INNER JOIN  `vw_lista_activos` ON `tbl_documentos`.`Entidad` = `vw_lista_activos`.`IdActivo` AND `tbl_documentos`.`IdEntidad` = `vw_lista_activos`.`IdActivoData`
WHERE `tbl_documentos`.`Entidad` >= 10
	AND `tbl_documentos`.`IdEstatus` = 5
) as A
	INNER JOIN `tbl_tipos_documentos` ON `tbl_tipos_documentos`.`IdTipoDocumento` = a.IdTipoDocumento
	INNER JOIN `tbl_documentos_estatus` ON `tbl_documentos_estatus`.`IdEstatus` = a.IdEstatus
	INNER JOIN `tbl_entidades` ON `tbl_entidades`.`IdEntidad` = a.Entidad";
		return $sql;
	}	

	public static function queryWhere(  ){
            $filter = "  ";

		return $filter;
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
