<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblevalfinanciera extends Sximo  {
	
	protected $table = 'tbl_eval_financiera';
	protected $primaryKey = 'fin_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_eval_financiera.* FROM tbl_eval_financiera  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_eval_financiera.fin_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
