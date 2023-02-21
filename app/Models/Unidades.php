<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class unidades extends Sximo  {
	
	protected $table = 'tbl_unidades';
	protected $primaryKey = 'IdUnidad';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_unidades.* FROM tbl_unidades  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_unidades.IdUnidad IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
