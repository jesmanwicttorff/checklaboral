<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class segmentos extends Sximo  {
	
	protected $table = 'tbl_contsegmento';
	protected $primaryKey = 'segmento_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contsegmento.* FROM tbl_contsegmento  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_contsegmento.segmento_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
