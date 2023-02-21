<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class documentovalorhistorico extends Sximo  {
	
	protected $table = 'tbl_documento_valor_historico';
	protected $primaryKey = 'IdDocumentoValor';
	public $timestamps = false;

	public function __construct() {
		parent::__construct();
		
	}

	public function Documentos(){
		return $this->belongsTo('App\Models\Documentoshistorico', 'IdDocumento', 'IdDocumento');
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

}
