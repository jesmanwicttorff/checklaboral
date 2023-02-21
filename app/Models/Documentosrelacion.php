<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class documentosrelacion extends Sximo  {
	
	protected $table = 'tbl_documentos';
	protected $primaryKey = 'IdDocumento';
	const CREATED_AT = 'createdOn';
    const UPDATED_AT = 'updatedOn';

	public function __construct() {
		parent::__construct();
	}
	public function FormatoDocumento() {
		return $this->belongsTo('App\Models\Formatosdocumentos', 'IdFormato', 'id');
	}
	public function TipoDocumento()
    {
        return $this->belongsTo('App\Models\Tipodocumentos', 'IdTipoDocumento', 'IdTipoDocumento');
    }
    public function EntidadRelacion(){
    	return $this->belongsTo('App\Models\Entidades', 'Entidad', 'IdEntidad');
    }
    public function Estatus(){
    	return $this->belongsTo('App\Models\Documentosestatus', 'IdEstatus', 'IdEstatus');
    }
	public function Documentovalor() {
		return $this->hasMany('App\Models\Documentovalor', 'IdDocumento', 'IdDocumento');
	}
	public function Anexos() {
		return $this->hasOne('App\Models\Tbldocumentosanexos', 'IdDocumento', 'IdDocumento');
	}
	public function Bitacora() {
		return $this->hasMany('App\Models\Documentoslog', 'IdDocumento', 'IdDocumento');
	}
	public function Versiones() {
		$lobjInstancia = $this->hasMany('App\Models\Documentoslog', 'IdDocumento', 'IdDocumento');
		$lobjInstancia->where('IdAccion','16');
		return $lobjInstancia;
	}

	public static function querySelect(  ){
		
		return " SELECT `tbl_documentos`.*,
				  case when `tbl_documentos`.`Entidad` = 1 then CONCAT(`tbl_contratistas`.`RUT`,' ',`tbl_contratistas`.`RazonSocial`)
				       when `tbl_documentos`.`Entidad` = 2 then CONCAT(`tbl_contrato`.`cont_proveedor`,' ',`tbl_contrato`.`cont_numero`)
				       when `tbl_documentos`.`Entidad` = 3 then CONCAT(`tbl_personas`.`RUT`,' ', ifnull(`tbl_personas`.`Nombres`,''),' ', ifnull(`tbl_personas`.`Apellidos`,''))
				       when `tbl_documentos`.`Entidad` = 6 then tbl_centro.descripcion
				       when `tbl_documentos`.`Entidad` = 9 then CONCAT(`tc`.`RUT`,' ',`tc`.`RazonSocial`)
				       when `tbl_documentos`.`Entidad` >= 10 then CONCAT(`vw_lista_activos`.`Activo`,' ',`vw_lista_activos`.`Valor`) else 'No especificado' end as Detalle, 
				       `tbl_tipos_documentos`.`group_id`,
				       `tbl_tipos_documentos`.`Tipo`,
				       `tbl_tipos_documentos`.`Vigencia`,
				       `tbl_tipos_documentos`.`DiasVencimiento`,
				       `tbl_tipos_documentos`.`IdProceso`,
				       `tbl_tipos_documentos`.`IdFormato`,
				       `tbl_documentos`.`Vencimiento`
				FROM `tbl_documentos`
				INNER JOIN `tbl_tipos_documentos` ON `tbl_documentos`.`IdTipoDocumento` =  `tbl_tipos_documentos`.`IdTipoDocumento`
				LEFT JOIN `tbl_contratistas` ON `tbl_documentos`.`Entidad` = 1 AND `tbl_documentos`.`IdEntidad` = `tbl_contratistas`.`IdContratista`
				LEFT JOIN `tbl_contrato` ON `tbl_documentos`.`Entidad` = 2 AND `tbl_documentos`.`IdEntidad` = `tbl_contrato`.`contrato_id`
				LEFT JOIN  `tbl_personas` ON `tbl_documentos`.`Entidad` = 3 AND `tbl_documentos`.`IdEntidad` = `tbl_personas`.`IdPersona`
				LEFT JOIN  `tbl_centro` ON `tbl_documentos`.`Entidad` = 6 AND `tbl_documentos`.`IdEntidad` = `tbl_centro`.`IdCentro`
				LEFT JOIN  `tbl_contratos_subcontratistas` ON `tbl_documentos`.`Entidad` = 9 AND `tbl_documentos`.`IdEntidad` = `tbl_contratos_subcontratistas`.`IdSubContratista` AND `tbl_documentos`.`contrato_id` = `tbl_contratos_subcontratistas`.`contrato_id`
				LEFT JOIN `tbl_contratistas` as tc ON `tbl_documentos`.`Entidad` = 9 AND `tbl_contratos_subcontratistas`.`IdSubContratista`= `tc`.`IdContratista`
				LEFT JOIN  `vw_lista_activos` ON `tbl_documentos`.`Entidad` >= 10 AND `tbl_documentos`.`Entidad` = `vw_lista_activos`.`IdActivo` AND `tbl_documentos`.`IdEntidad` = `vw_lista_activos`.`IdActivoData` ";
	
	}	

	public static function queryWhere(  ){
		
		return " WHERE tbl_documentos.IdDocumento IS NOT NULL and tbl_documentos.IdEstatus != 99  ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	
	protected static function boot() {
        parent::boot();

        static::deleting(function($pobjdocumentos) { // before delete() method call this
             $pobjdocumentos->Bitacora()->delete();
             $pobjdocumentos->Documentovalor()->delete();
        });
        
    }

}
