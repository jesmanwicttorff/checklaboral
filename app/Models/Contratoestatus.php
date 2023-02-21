<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class contratoestatus extends Sximo  {
	
	protected $table = 'tbl_contrato_estatus';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contrato_estatus.* FROM tbl_contrato_estatus  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contrato_estatus.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
