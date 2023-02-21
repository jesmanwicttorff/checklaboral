<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class ldalertsindcontroller extends Sximo  {
	
	protected $table = 'tbl_alertas';
	protected $primaryKey = 'alertas_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_alertas.*, CASE WHEN alerta_activa = 0 THEN 'Desactivada' ELSE 'Activada' END estado_al FROM tbl_alertas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_alertas.alertas_id IS NOT NULL and tipo_alerta = 1 and tipo_prog = 'MANUAL' ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
