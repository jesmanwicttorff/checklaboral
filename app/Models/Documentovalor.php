<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class documentovalor extends Sximo  {
	
	protected $table = 'tbl_documento_valor';
	protected $primaryKey = 'IdDocumentoValor';

	public function __construct() {
		parent::__construct();
		
	}

	public function Documentos(){
		return $this->belongsTo('App\Models\Documentos', 'IdDocumento', 'IdDocumento');
	}

	public function Tipodocumentovalor(){
		return $this->belongsTo('App\Models\Tipodocumentovalor', 'IdTipoDocumentoValor', 'IdTipoDocumentoValor');
	}

	public function scopeActive($query){
		return $query->where('IdEstatus',1);
	}

	public function scopeTipovalor($query, $lintIdTipoValor)
    {
        return $query->where('IdTipoDocumentoValor', $lintIdTipoValor);
    }

	public static function querySelect(  ){
		
		return "  SELECT tbl_documento_valor.* FROM tbl_documento_valor  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_documento_valor.IdDocumentoValor IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
