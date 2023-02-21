<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class documentosloghistorico extends Sximo  {
	
	protected $table = 'tbl_documentos_log_historico';
	protected $primaryKey = 'id';
	const CREATED_AT = 'createdOn';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_documentos_log_historico.* FROM tbl_documentos_log_historico  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_documentos_log_historico.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
