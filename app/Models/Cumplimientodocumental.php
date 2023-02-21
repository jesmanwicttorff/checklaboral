<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class cumplimientodocumental extends Sximo  {
	
	protected $table = 'tbl_cump_documental';
	protected $primaryKey = 'idcump_doc';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_cump_documental.* FROM tbl_cump_documental  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_cump_documental.idcump_doc IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
