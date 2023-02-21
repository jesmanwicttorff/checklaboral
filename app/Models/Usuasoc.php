<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class usuasoc extends Sximo  {
	
	protected $table = 'tb_assocc';
	protected $primaryKey = 'idAssocc';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tb_assocc.* FROM tb_assocc  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tb_assocc.idAssocc IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
