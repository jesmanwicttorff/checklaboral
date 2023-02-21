<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestasmastercategoria extends Sximo  {
	
	protected $table = 'tbl_encuestas_master_categoria';
	protected $primaryKey = 'IdEncuestaMasterCategoria';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_encuestas_master_categoria.* FROM tbl_encuestas_master_categoria  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_encuestas_master_categoria.IdEncuestaMasterCategoria IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
