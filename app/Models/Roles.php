<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class roles extends Sximo  {
	
	protected $table = 'tbl_roles';
	protected $primaryKey = 'IdRol';

	public function __construct() {
		parent::__construct();
		
	}

	public function Servicios()
    {
        return $this->hasMany('App\Models\Rolesservicios', 'IdRol', 'idrol');
    }

	public static function querySelect(  ){
		
		return "  SELECT tbl_roles.*
FROM tbl_roles  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_roles.IdRol IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
