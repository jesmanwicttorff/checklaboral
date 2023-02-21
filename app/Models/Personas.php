<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class personas extends Sximo  {
	
	protected $table = 'tbl_personas';
	protected $primaryKey = 'IdPersona';

	public function __construct() {
		parent::__construct();
		
	}

	public function Contratospersonas(){
		return $this->hasOne('App\Models\Contratospersonas', 'IdPersona', 'IdPersona');
	}

	public function DocumentoContractual()
    {
        return $this->hasOne('App\Models\Documentos', 'IdEntidad', 'IdPersona')
        ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
        ->where('tbl_tipos_documentos.idproceso',21)
        ->where('tbl_documentos.entidad',3);
    }

	public static function querySelect(  ){
		
		
		return " SELECT tbl_personas.*, case when tbl_accesos.`IdEstatus` = 1 then 
						          '<span class=\"label label-primary\"> Acréditado </span>'
						     when tbl_accesos.`IdEstatus` = 2 then
						     	  '<span class=\"label label-danger\"> Sin acreditación </span>'
						     else 
						     	  '-'
						     end as IdEstatusAcreditacion ,  tbl_contrato.contrato_id, tbl_contrato.cont_numero, concat(first_name, ' ', last_name) entry_by_name, tbl_nacionalidad.nacionalidad, tbl_contrato.cont_nombre, tbl_roles.Descripción as Rol
				 FROM tbl_personas
				 LEFT JOIN tbl_contratos_personas ON  tbl_personas.IdPersona = tbl_contratos_personas.IdPersona 
				 LEFT JOIN tbl_contrato ON tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id
				 LEFT JOIN tbl_roles ON tbl_contratos_personas.IdRol = tbl_roles.IdRol
				 LEFT JOIN tb_users ON tb_users.id = tbl_personas.entry_by
				 LEFT JOIN tbl_nacionalidad ON tbl_nacionalidad.id_Nac = tbl_personas.id_Nac
				 LEFT JOIN tbl_accesos ON tbl_accesos.IdPersona = tbl_personas.IdPersona AND tbl_accesos.IdTipoAcceso = 1";

	}	

	public static function queryWhere(  ){
			return "  WHERE tbl_personas.IdPersona IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	public function nacionalidad(){
		
		return $this->hasOne('App\Models\Nacionalidades', 'id_Nac', 'id_Nac');
	}

	public function documentos(){
		
		return $this->hasMany('App\Models\Documentos', 'IdEntidad', 'IdPersona')->where('tbl_documentos.entidad',3);
	
	}

	public function entryBy(){

		return $this->hasOne('App\Models\Core\Users', 'id', 'entry_by');
	}

	public function movimientosPersonales(){
		
		return $this->hasMany('App\Models\MovimientoPersonal', 'IdPersona', 'IdPersona');
	}
	

}
