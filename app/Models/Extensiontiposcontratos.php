<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class extensiontiposcontratos extends Sximo  {
	
	protected $table = 'tbl_extension_tipos_contratos';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_extension_tipos_contratos.* FROM tbl_extension_tipos_contratos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_extension_tipos_contratos.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
