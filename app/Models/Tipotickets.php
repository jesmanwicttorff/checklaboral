<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tipotickets extends Sximo  {
	
	protected $table = 'lod_help_topic';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT lod_help_topic.* FROM lod_help_topic  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE lod_help_topic.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
