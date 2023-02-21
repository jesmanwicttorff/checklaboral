<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class mailalertctrlpers extends Sximo  {
	
	protected $table = 'tbl_personas_alertas';
	protected $primaryKey = 'id_pers_alert';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_personas_alertas.*,case when alerta_mail = 0 then 'No' else 'Si' end alerta_m, case when alerta_dashboard = 0 then 'No' else 'Si' end alerta_d FROM tbl_personas_alertas  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_personas_alertas.id_pers_alert IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
