<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class preguntas extends Sximo  {
	
	protected $table = 'tbl_preguntas';
	protected $primaryKey = 'IdPregunta';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_preguntas.* FROM tbl_preguntas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_preguntas.IdPregunta IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
