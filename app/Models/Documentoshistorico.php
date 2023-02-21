<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class documentoshistorico extends Sximo  {
	
	protected $table = 'tbl_documentos_rep_historico';
	protected $primaryKey = 'IdDocumentoH';

	public function __construct() {
		parent::__construct();
	}

	public function TipoDocumento()
    {
        return $this->belongsTo('App\Models\Tipodocumentos', 'IdTipoDocumento', 'IdTipoDocumento');
    }

    public function EntidadRelacion(){
    	return $this->belongsTo('App\Models\Entidades', 'Entidad', 'IdEntidad');
    }

	public function Documentovalor() {
		return $this->hasMany('App\Models\Documentovalorhistorico', 'IdDocumento', 'IdDocumento');
	}
	public function Anexos() {
		return $this->hasOne('App\Models\Tbldocumentosanexos', 'IdDocumento', 'IdDocumento');
	}

}
