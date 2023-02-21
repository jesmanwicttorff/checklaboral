<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class usralertctrl extends Sximo  {
	
	protected $table = 'tbl_alertas_mail';
	protected $primaryKey = 'id_mail';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_alertas_mail.*, case when alerta_mail = 0 then 'No' else 'Si' end alerta_m, case when alerta_dashboard = 0 then 'No' else 'Si' end alerta_d FROM tbl_alertas_mail  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_alertas_mail.id_mail IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
