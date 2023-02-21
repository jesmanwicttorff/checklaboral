<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class reportglobalnegociofisicofinanciero extends Sximo  {
	
	protected $table = 'projections_sample';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT projections_sample.* FROM projections_sample  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE projections_sample.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
