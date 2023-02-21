<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class maeasociaciongrup extends Sximo  {
	
	protected $table = 'tb_assoccgroup';
	protected $primaryKey = 'idAssoccGroup';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tb_assoccgroup.* FROM tb_assoccgroup  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tb_assoccgroup.idAssoccGroup IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
