<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class contratos extends Sximo  {

	protected $table = 'tbl_contrato';
	protected $primaryKey = 'contrato_id';
	const CREATED_AT = 'createdOn';
    const UPDATED_AT = 'updatedOn';

	public function __construct() {
		parent::__construct();

	}

	public function scopeDisponibleAsignacion($query){
		$query->join("tbl_contrato_estatus","tbl_contrato_estatus.id","=","tbl_contrato.cont_estado")->where("tbl_contrato_estatus.BloqueaVinculacion","0");
	}

	public function Contratista(){
		return $this->belongsTo('App\Models\Contratistas', 'IdContratista', 'IdContratista');
	}

	public function Estatus()
    {
        return $this->belongsTo('App\Models\Contratoestatus', 'cont_estado', 'id');
    }

    public function Accesos()
    {
        return $this->hasMany('App\Models\Accesos', 'contrato_id', 'contrato_id');
    }

    public function DocumentoContractual()
    {
        return $this->hasOne('App\Models\Documentos', 'IdEntidad', 'contrato_id')
        ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
        ->where('tbl_tipos_documentos.idproceso',26)
        ->where('tbl_documentos.entidad',2);
    }

	public static function querySelect(  ){

		return " SELECT tbl_contrato.contrato_id,
				        tbl_contrato.IdContratista,
				        tbl_contrato.cont_nombre,
				        tbl_contrato.cont_rutp_sd,
				        tbl_contrato.cont_rutp_dv,
				        tbl_contrato.cont_numero,
				        tbl_contrato.cont_proveedor,
				        tbl_contrato.categoria_id,
				        tbl_contrato.cont_fechaInicio,
				        tbl_contrato.cont_fechaFin,
								tbl_contrato.cont_fechaInicioContrato,
				        tbl_contrato.cont_montoTotal,
				        tbl_contrato.IdGarantia,
				        tbl_contrato.cont_garantia,
				        tbl_contrato.cont_garantia_sugerida,
				        tbl_contrato.admin_id,
				        tbl_contrato.segmento_id,
				        tbl_contrato.geo_id,
				        tbl_contrato.cont_compagnia,
                        tbl_contrato.id_extension,
				        tbl_contrato.afuncional_id,
				        tbl_contrato.claseCosto_id,
				        tbl_contrato.cont_fechaEstado,
				        tbl_contrato.cont_glosaDescriptiva,
						tbl_contrato.cont_estado,
				        tbl_contrato_estatus.nombre as cont_estado_desc,
				        tbl_contrato.firma_id,
				        tbl_contrato.entry_by,
				        tbl_contrato.impacto,
						tbl_contrato.complejidad,
				        concat(tb_users.first_name, ' ', tb_users.last_name) as entry_by_name,
				        tbl_contrato.entry_by_access,
				        tbl_contrato.createdOn,
				        tbl_contrato.updatedOn,
				        tbl_contrato.id_extension,
					    CASE WHEN tbl_contrato.firma_id = 1 THEN 'FIRMADO'
					    ELSE 'SIN FIRMAR' END estado_firma,
					    tbl_contrato.firma_fecha,
					    tbl_contrato.cont_gestor,
						tbl_contrato.controlareporte,
						tbl_contrato.impacto,
						tbl_contrato.complejidad,
						tbl_contrato.autorenovacion,
						tbl_contrato.plazoautorenovacion,
					    case when tbl_contrato.controlareporte = 1 then 'SI' else 'NO' end as controlareportedesc,
					    (SELECT COUNT(DISTINCT tbl_contratos_personas.IdPersona)
					     FROM tbl_contratos_personas
					     WHERE tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id) as cant_pers,
						tbl_contrato.IdComplejidad,
						tbl_contrato.IdMoneda,
						tbl_contrato.FechaAdjudicacion,
						tbl_contrato.ContratoMarco,
						tbl_contrato.Solped,
						tbl_contrato.idgrupoespecifico,
						tbl_contrato.idservicio,
						tbl_contrato.MontoContratoMarco,
	 					tbl_contrato.IdTipoAdjudicacion,
            			tbl_contrato.ProveedorUnico,
            			tbl_contrato.JustificacionProveedorUnico,
            			tbl_contrato.LibroObra,
            			tbl_contrato.IdAdministradorContratista,
            			tbl_contrato.Reajustado
				 FROM tbl_contrato
				 INNER JOIN tbl_contrato_estatus ON tbl_contrato.cont_estado = tbl_contrato_estatus.id
				 LEFT JOIN tb_users ON tbl_contrato.entry_by = tb_users.id ";
	}

	public static function queryWhere(  ){

		return "  WHERE tbl_contrato.contrato_id IS NOT NULL ";
	}

	public static function queryGroup(){
		return "  ";
	}

	public function edps(){

		return $this->hasMany('App\Models\Edp', 'contrato_id', 'contrato_id' );
	
	}
	public function contratoItemizado(){
        
		return $this->hasOne('App\Models\ContratoItemizado','contrato_id', 'contrato_id');
    
	}

	public function confAnexo(){
        
		return $this->hasOne('App\Models\Confanexoscontrato','id_extension', 'id_extension');
    
	}



}
