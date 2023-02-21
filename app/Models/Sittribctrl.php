<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class sittribctrl extends Sximo  {
	
	protected $table = 'tbl_contratista_tributario';
	protected $primaryKey = 'tributarioContratista';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contratista_tributario.* FROM tbl_contratista_tributario  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contratista_tributario.tributarioContratista IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
