<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tipodocumentosperfil extends Sximo  {
	
	public $timestamps = false;
	protected $table = 'tbl_tipo_documento_perfil';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_tipo_documento_perfil.* FROM tbl_tipo_documento_perfil  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tipo_documento_perfil.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	
}
