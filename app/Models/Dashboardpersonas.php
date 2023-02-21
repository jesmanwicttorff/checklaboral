<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class dashboardpersonas extends Sximo  {
	
	protected $table = 'tbl_personas';
	protected $primaryKey = 'IdPersona';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_personas.* FROM tbl_personas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_personas.IdPersona IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
