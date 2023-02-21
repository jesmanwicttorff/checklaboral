<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class msgalertascontroller extends Sximo  {
	
	protected $table = 'tbl_alerta_msg';
	protected $primaryKey = 'id_mensaje';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_alerta_msg.*  FROM tbl_alerta_msg  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_alerta_msg.id_mensaje IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
