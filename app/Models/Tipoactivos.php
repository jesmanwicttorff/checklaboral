<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tipoactivos extends Sximo  {
	
	protected $table = 'tbl_activos';
	protected $primaryKey = 'IdActivo';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return " SELECT tbl_activos.IdActivo,
					    tbl_activos.Descripcion,
				        case when tbl_activos.ControlaAcceso = 1 then 'Sí' else 'No' end as ControlaAcceso,
				        case when tbl_activos.RequiereCertificacion = 1 then 'Sí' else 'No' end as RequiereCertificacion,
				        tbl_activos.IconoActivo,
				        case when tbl_activos.IdTipoAcceso = 1 then 'Personas'
				            when tbl_activos.IdTipoAcceso = 2 then 'Vehiculos'
				            when tbl_activos.IdTipoAcceso = 3 then 'Ambos'
				            else '-' end as IdTipoAcceso,
				        case when tbl_activos.IdEstatus = 1 then 'Activo' else 'Inactivo' end as IdEstatus,
				        tbl_activos.entry_by,
				        tbl_activos.createdOn,
				        tbl_activos.entry_by_access
				 FROM tbl_activos; ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_activos.IdActivo IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
