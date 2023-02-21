<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class georeporte extends Sximo  {
	
	protected $table = 'tbl_contgeografico';
	protected $primaryKey = 'geo_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contgeografico.* FROM tbl_contgeografico  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contgeografico.geo_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
