<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class contratospersonas extends Sximo  {

	protected $table = 'tbl_contratos_personas';
	protected $primaryKey = 'IdContratosPersonas';
	const CREATED_AT = 'createdOn';
    const UPDATED_AT = 'updatedOn';

	public function __construct() {
		parent::__construct();
	}

	public function Persona()
    {
        return $this->belongsTo('App\Models\Personas', 'IdPersona', 'IdPersona');
    }

	public function Contrato()
    {
        return $this->belongsTo('App\Models\Contratos', 'contrato_id', 'contrato_id');
    }

    public function TipoContrato()
    {
        return $this->belongsTo('App\Models\Tiposcontratospersonas', 'IdTipoContrato', 'id');
    }

	public function Estatus()
    {
        return $this->belongsTo('App\Models\Documentoestatus', 'IdEstatus', 'IdEstatus');
    }

    public function Rol()
    {
        return $this->belongsTo('App\Models\Roles', 'IdRol', 'IdRol');
    }

	public static function querySelect(  ){

		return "  SELECT CASE WHEN tbl_contrato.IdContratista = tbl_contratistas.IdContratista THEN 0 ELSE 1 END AS Tipo,  tbl_contratistas.RazonSocial, tbl_contratos_personas.*,  tbl_personas.RUT, tbl_personas.Nombres, tbl_personas.Apellidos, tb_users.first_name, tb_users.last_name, tbl_roles.Descripción as Rol
		          FROM tbl_contratos_personas
		          INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
		          INNER JOIN tbl_contratistas ON tbl_contratistas.IdContratista = tbl_contratos_personas.IdContratista
		          INNER JOIN tbl_personas ON tbl_personas.IdPersona = tbl_contratos_personas.IdPersona
		          INNER JOIN tb_users ON tbl_personas.entry_by_access = tb_users.id
		          INNER JOIN tbl_roles ON tbl_contratos_personas.IdRol = tbl_roles.IdRol ";
	}

	public static function queryWhere(  ){
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');
	    if ($lintLevelUser==6){
		  return "  WHERE tbl_contratos_personas.IdContratosPersonas IS NOT NULL
		                AND ( tbl_contratos_personas.entry_by_access = ".$lintIdUser."  ) ";
		}else{
		  return "  WHERE tbl_contratos_personas.IdContratosPersonas IS NOT NULL ";
		}
	}

	public static function queryGroup(){
		return "  ";
	}

	protected $fillable = ['contrato_id', 'IdContratista', 'IdRol', 'FechaInicioFaena', 'Acreditacion', 'entry_by', 'entry_by_access'];

}
