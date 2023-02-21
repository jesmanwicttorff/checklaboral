<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class confanexoscontrato extends Sximo  {
	
	protected $table = 'tbl_conf_anexos';
	protected $primaryKey = 'IdConfAnexos';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_conf_anexos.* FROM tbl_conf_anexos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_conf_anexos.IdConfAnexos IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
