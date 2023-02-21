<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class accesos extends Sximo  {
	
	protected $table = 'tbl_accesos';
	protected $primaryKey = 'IdAcceso';
	const CREATED_AT = 'createdOn';
    const UPDATED_AT = 'updatedOn';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){

		return " select * from (SELECT tbl_accesos.`IdAcceso` , 
						tbl_accesos.`IdTipoAcceso` , 
						tbl_personas.`IdPersona` , 
						tbl_accesos.`contrato_id` , 
						tbl_accesos.`FechaInicio` , 
						tbl_accesos.`FechaFinal` , 
						case when tbl_accesos.`IdEstatus` = 1 then 
						          '<span class=\"label label-primary\"> Con Acceso </span>'
						     when tbl_accesos.`IdEstatus` = 2 then
						     	  '<span class=\"label label-danger\"> Sin Acceso </span>'
						     when tbl_accesos.`IdEstatusUsuario` = 3 then
						     	  '<span class=\"label label-success\"> Acceso Temporal </span>'
						     end as IdEstatus , 
						case when tbl_accesos.`IdEstatusUsuario` = 1 then 
						          '<span class=\"label label-primary\"> Con Acceso </span>'
						     when tbl_accesos.`IdEstatusUsuario` = 2 then
						     	  '<span class=\"label label-danger\"> Sin Acceso </span>'
						     when tbl_accesos.`IdEstatusUsuario` = 3 then
						     	  '<span class=\"label label-success\"> Acceso Temporal </span>'
						     end as IdEstatusUsuario , 
						tbl_accesos.`updatedOn` , 
						tbl_accesos.`entry_by` , 
						tbl_accesos.`createdOn` , 
						tbl_accesos.`IdSolicitudAcceso` , 
						tbl_accesos.`Observacion` , 
						tbl_accesos.`data_rut` , 
						tbl_accesos.`data_nombres` , 
						tbl_accesos.`data_apellidos` ,
						CONCAT(tbl_personas.RUT,' ', tbl_personas.Nombres,' ',tbl_personas.Apellidos ) AS data_concat,
						tbl_personas.`entry_by_access` 
				FROM tbl_accesos 
					INNER JOIN tbl_personas ON tbl_personas.IdPersona = tbl_accesos.IdPersona 
				UNION 
				SELECT 	tbl_accesos.`IdAcceso` , 
						tbl_accesos.`IdTipoAcceso` , 
						null `IdPersona` , 
						null `contrato_id` , 
						tbl_accesos.`FechaInicio` , 
						tbl_accesos.`FechaFinal` , 
						case when tbl_accesos.`IdEstatus` = 1 then 
						          '<span class=\"label label-primary\"> Con Acceso </span>'
						     when tbl_accesos.`IdEstatus` = 2 then
						     	  '<span class=\"label label-danger\"> Sin Acceso </span>'
						     end as IdEstatus , 
						case when tbl_accesos.`IdEstatusUsuario` = 1 then 
						          '<span class=\"label label-primary\"> Con Acceso </span>'
						     when tbl_accesos.`IdEstatusUsuario` = 2 then
						     	  '<span class=\"label label-danger\"> Sin Acceso </span>'
						     end as IdEstatusUsuario , 
						tbl_accesos.`updatedOn` , 
						tbl_accesos.`entry_by` , 
						tbl_accesos.`createdOn` , 
						tbl_accesos.`IdSolicitudAcceso` , 
						tbl_accesos.`Observacion` , 
						tbl_accesos.`data_rut` , 
						tbl_accesos.`data_nombres` , 
						tbl_accesos.`data_apellidos` , 
						CONCAT( tbl_accesos.`data_rut` , ' ' , tbl_accesos.`data_nombres` , ' ' , tbl_accesos.`data_apellidos`) AS data_concat,
						tbl_personas.`entry_by_access` 
				FROM tbl_accesos
				LEFT JOIN tbl_personas ON tbl_personas.rut = tbl_accesos.data_rut
				WHERE tbl_accesos.IdPersona = 0 ) tbl_accesos
"; 
	}	

	public static function queryWhere(  ){

		return "  WHERE tbl_accesos.IdAcceso IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
