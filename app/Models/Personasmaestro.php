<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class personasmaestro extends Sximo  {
	
	protected $table = 'tbl_personas_maestro';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public function Persona(){
		return $this->belongsTo('App\Models\Personas', 'IdPersona', 'id');
	}

	public function Contrato(){
		return $this->belongsTo('App\Models\Contratos', 'contrato_id', 'contrato_id');
	}
	
	public function Contratista(){
		return $this->belongsTo('App\Models\Contratistas', 'IdContratista', 'idcontratista');
	}
	

}
