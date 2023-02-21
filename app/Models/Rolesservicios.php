<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class rolesservicios extends Sximo  {
	
	protected $table = 'tbl_roles_servicios';

	public function __construct() {
		parent::__construct();
		
	}

	protected $fillable = ['idrol', 'idservicio'];

	public function Contratos() {
		return $this->hasMany('App\Models\Contratos', 'idservicio', 'idservicio');
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_roles_servicios.*
				  FROM tbl_roles_servicios  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_roles_servicios.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
