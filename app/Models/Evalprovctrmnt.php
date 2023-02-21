<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class evalprovctrmnt extends Sximo  {
	
	protected $table = 'tbl_evalcontratistas';
	protected $primaryKey = 'id_evaluacion';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_evalcontratistas.* FROM tbl_evalcontratistas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_evalcontratistas.id_evaluacion IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
