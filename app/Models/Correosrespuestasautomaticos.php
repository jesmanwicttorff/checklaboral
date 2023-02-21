<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class correosrespuestasautomaticos extends Sximo  {
	
	protected $table = 'lod_templates';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT lod_templates.* FROM lod_templates  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE lod_templates.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
