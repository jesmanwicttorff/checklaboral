<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Categoriadetalle extends Sximo  {
	
	protected $table = 'tbl_contratista_servicio';
	protected $primaryKey = 'IdContratistaServicio';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contratista_servicio.* FROM tbl_contratista_servicio  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contratista_servicio.IdContratistaServicio IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
