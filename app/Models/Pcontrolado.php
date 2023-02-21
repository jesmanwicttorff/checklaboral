<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class pcontrolado extends Sximo  {
	
	protected $table = 'tbl_periodo_controlado';
	protected $primaryKey = 'id_pcontrolado';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_periodo_controlado.* FROM tbl_periodo_controlado  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_periodo_controlado.id_pcontrolado IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
