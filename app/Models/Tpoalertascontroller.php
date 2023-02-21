<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tpoalertascontroller extends Sximo  {
	
	protected $table = 'tbl_alerta_tpo';
	protected $primaryKey = 'tipo_alerta';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_alerta_tpo.* FROM tbl_alerta_tpo  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_alerta_tpo.tipo_alerta IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
