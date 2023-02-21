<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class moduloalertasdetalle extends Sximo  {
	
	protected $table = 'tbl_alerta_detalle';
	protected $primaryKey = 'id_alerta_detalle';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_alerta_detalle.* FROM tbl_alerta_detalle  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_alerta_detalle.id_alerta_detalle IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
