<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class requisitos extends Sximo  {
	
	protected $table = 'tbl_requisitos';
	protected $primaryKey = 'IdRequisito';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_requisitos.*
 FROM tbl_requisitos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_requisitos.IdRequisito IS NOT NULL AND tbl_requisitos.Entidad IN (1,2,3,6) ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
