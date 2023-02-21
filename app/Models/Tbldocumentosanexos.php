<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Tbldocumentosanexos extends Sximo  {
	
	protected $table = 'tbl_documentos_anexos';
	protected $fillable = [ 'IdDocumento', 'contrato_id', 'IdRol','fecha_cambio', 'IdTipoContrato', 'FechaVencimiento', 'IdEstatus', 'entry_by'];

	public function Documento()
    {
        return $this->belongsTo('App\Models\Documentos', 'IdDocumento', 'IdDocumento');
    }

    public function Contrato()
    {
        return $this->belongsTo('App\Models\Contratos', 'contrato_id', 'contrato_id');
    }

    public function Tipocontrato()
    {
        return $this->belongsTo('App\Models\Tiposcontratospersonas', 'IdTipoContrato', 'id');
    }

    public function Rol()
    {
        return $this->belongsTo('App\Models\Roles', 'IdRol', 'IdRol');
    }

	public function __construct() {
		parent::__construct();
		
	}
	

}
