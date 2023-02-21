<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblcontratofisico extends Sximo  {
	
	protected $table = 'tbl_fisico_and';
	protected $primaryKey = 'fisicoand_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_fisico_and.* FROM tbl_fisico_and  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_fisico_and.fisicoand_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
