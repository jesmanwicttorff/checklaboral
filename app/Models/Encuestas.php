<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class encuestas extends Sximo  {
	
	protected $table = 'tbl_encuestas';
	protected $primaryKey = 'IdEncuesta';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_encuestas.* FROM tbl_encuestas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_encuestas.IdEncuesta IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
