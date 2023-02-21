<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestados extends Sximo  {
	
	protected $table = 'tbm_encuestas';
	protected $primaryKey = 'encuesta_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbm_encuestas.* FROM tbm_encuestas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbm_encuestas.encuesta_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}

	public function categorias(){
		
		return $this->belongsToMany('App\Models\Encuestadoscategorias', 'tbm_relacion_encuesta_categoria_pregunta', 'encuesta_id', 'categoriaPregunta_id');
	}

	public function preguntas(){

		return $this->belongsToMany('App\Models\Tipodocumentovalor', 'tbm_relacion_encuesta_categoria_pregunta', 'encuesta_id', 'pregunta_id');
	}

}
