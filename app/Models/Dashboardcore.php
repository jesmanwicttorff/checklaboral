<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class dashboardcore extends Sximo  {
	
	protected $table = 'tbl_documentos';
	protected $primaryKey = 'IdDocumento';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_documentos.* FROM tbl_documentos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_documentos.IdDocumento IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
