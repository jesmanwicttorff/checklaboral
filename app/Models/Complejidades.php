<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class complejidades extends Sximo  {
	
	protected $table = 'complejidades';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT complejidades.* FROM complejidades  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE complejidades.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
