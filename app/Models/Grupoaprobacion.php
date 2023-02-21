<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class grupoaprobacion extends Sximo  {
	
	public $timestamps = false;
	protected $table = 'tbl_perfil_aprobacion';
	protected $primaryKey = 'IdGrupoAprobacion';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_perfil_aprobacion.* FROM tbl_perfil_aprobacion  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_perfil_aprobacion.IdGrupoAprobacion IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
