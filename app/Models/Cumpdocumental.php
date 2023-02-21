<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class cumpdocumental extends Sximo  {
	
	protected $table = 'tbl_cump_documental';
	protected $primaryKey = '';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_cump_documental.* FROM tbl_cump_documental  ";
	}	

	public static function queryWhere(  ){
		
		return " where tbl_cump_documental.idcump_doc is not null ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
