<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class moduloalertasmaster extends Sximo  {
	
	protected $table = 'tbl_alerta_master';
	protected $primaryKey = 'id_alerta_master';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_alerta_master.* FROM tbl_alerta_master  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_alerta_master.id_alerta_master IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
