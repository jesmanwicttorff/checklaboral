<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestasmaster extends Sximo  {
	
	protected $table = 'tbl_encuestas_master';
	protected $primaryKey = 'IdEncuestaMaster';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_encuestas_master.* FROM tbl_encuestas_master  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_encuestas_master.IdEncuestaMaster IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
