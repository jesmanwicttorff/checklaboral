<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class extensioncontrato extends Sximo  {
	
	protected $table = 'tbl_contrato_extension';
	protected $primaryKey = 'id_extension';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contrato_extension.*, case when tbl_contrato_extension.GeneraAnexo = 1 then 'SI' else 'NO' end GeneraAnexoDescripcion FROM tbl_contrato_extension  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contrato_extension.id_extension IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
