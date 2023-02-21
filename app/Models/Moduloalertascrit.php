<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class moduloalertascrit extends Sximo  {
	
	protected $table = 'tbl_alerta_crit';
	protected $primaryKey = 'id_crit';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_alerta_crit.* FROM tbl_alerta_crit  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_alerta_crit.id_crit IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
