<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestascategoria extends Sximo  {
	
	protected $table = 'tbl_encuestas_categoria';
	protected $primaryKey = 'IdEncuestaCategoria';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_encuestas_categoria.* FROM tbl_encuestas_categoria  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_encuestas_categoria.IdEncuestaCategoria IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
