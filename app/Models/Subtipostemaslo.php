<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class subtipostemaslo extends Sximo  {
	
	protected $table = 'tbl_tickets_subtipos';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function scopeActivos($query){
		return $query->where('IdEstatus',1);
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_tickets_subtipos.* FROM tbl_tickets_subtipos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tickets_subtipos.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
