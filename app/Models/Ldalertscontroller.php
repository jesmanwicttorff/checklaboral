<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class ldalertscontroller extends Sximo  {
	
	protected $table = 'tbl_alertas';
	protected $primaryKey = 'alertas_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_alertas.* FROM tbl_alertas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_alertas.alertas_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
