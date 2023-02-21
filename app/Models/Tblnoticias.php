<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblnoticias extends Sximo  {
	
	protected $table = 'tbl_noticiasglobales';
	protected $primaryKey = 'id_noticias';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_noticiasglobales.* FROM tbl_noticiasglobales  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_noticiasglobales.id_noticias IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
