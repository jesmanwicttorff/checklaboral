<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class motivorechazo extends Sximo  {
	
	protected $table = 'tbl_motivo_rechazo';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_motivo_rechazo.* FROM tbl_motivo_rechazo  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_motivo_rechazo.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
