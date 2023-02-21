<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tiposcontratospersonas extends Sximo  {
	
	protected $table = 'tbl_tipos_contratos_personas';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public function scopeActive($query){
		return $query->where('IdEstatus',1);
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_tipos_contratos_personas.* FROM tbl_tipos_contratos_personas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tipos_contratos_personas.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
