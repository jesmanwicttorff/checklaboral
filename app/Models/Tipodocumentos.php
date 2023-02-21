<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tipodocumentos extends Sximo  {
	
	protected $table = 'tbl_tipos_documentos';
	protected $primaryKey = 'IdTipoDocumento';

	public function __construct() {
		parent::__construct();
		
	}

	public function Aprobadores() {
		return $this->hasMany('App\Models\Aprobadores', 'IdTipoDocumento', 'IdTipoDocumento');
	}
	
	public function Valores() {
		return $this->hasMany('App\Models\Tipodocumentovalor', 'IdTipoDocumento', 'IdTipoDocumento');
	}

	public function scopeTipo($query, $pintIdTipoDocumento){
		$query->where('IdProceso',$pintIdTipoDocumento);
	}

	public static function querySelect(  ){
		
		return "  SELECT  tbl_tipos_documentos.*, 
				          case when permanencia = 0 then 'Sin permanencia' 
				               when permanencia = 1 then 'Hasta el vencimiento' 
				               when permanencia = 2 then 'Hasta el fin del contrato' 
				               else 'Sin especificar' end TipoPermanencia
		          FROM tbl_tipos_documentos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tipos_documentos.IdTipoDocumento IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}

	public function categorias(){
		
		return $this->belongsToMany('App\Models\Encuestadoscategorias', 'tbm_relacion_encuesta_categoria_pregunta','IdTipoDocumentoValor', 'categoriaPregunta_id');
	}

	

}
