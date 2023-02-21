<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestadoscategorias extends Sximo  {
	
	protected $table = 'tbm_encuestas_categorias_preguntas';
	protected $primaryKey = 'categoriaPregunta_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbm_encuestas_categorias_preguntas.* FROM tbm_encuestas_categorias_preguntas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbm_encuestas_categorias_preguntas.categoriaPregunta_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	public function encuestas(){

		return $this->belongsToMany('App\Models\Encuestados', 'tbm_relacion_encuesta_categoria_pregunta','categoriaPregunta_id', 'encuesta_id');
	}

	public function preguntas(){

		return $this->belongsToMany('App\Models\Tipodocumentos', 'tbm_relacion_encuesta_categoria_pregunta','categoriaPregunta_id','pregunta_id');
	}
	

}
