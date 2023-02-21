<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class preguntcategorias extends Sximo  {
	
	protected $table = 'tbl_preguncategorias';
	protected $primaryKey = 'IdCategoria';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_preguncategorias.* FROM tbl_preguncategorias  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_preguncategorias.IdCategoria IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
