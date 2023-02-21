<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class requisito extends Sximo  {
	
	protected $table = 'tbl_requisitos';
	protected $primaryKey = 'IdRequisito';

	public function __construct() {
		parent::__construct();
	}

	public function Detalles() {
		return $this->hasMany('App\Models\Requisitosdetalle', 'IdRequisito', 'IdRequisito');
	}
	
	public function TipoDocumento() {
		return $this->belongsTo('App\Models\Tipodocumentos', 'IdTipoDocumento', 'IdTipoDocumento');
	}
	public function Entidades() {
		return $this->belongsTo('App\Models\Entidades', 'Entidad', 'IdEntidad');
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_requisitos.* FROM tbl_requisitos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_requisitos.IdRequisito IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}

	

}
