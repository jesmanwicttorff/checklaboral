<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestadospreguntas extends Sximo  {
	
	protected $table = 'tbm_encuestas_preguntas';
	protected $primaryKey = 'pregunta_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbm_encuestas_preguntas.* FROM tbm_encuestas_preguntas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbm_encuestas_preguntas.pregunta_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}

}
