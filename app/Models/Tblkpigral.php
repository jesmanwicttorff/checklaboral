<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblkpigral extends Sximo  {
	
	protected $table = 'tbl_kpigral';
	protected $primaryKey = 'id_kpi';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_kpigral.*, concat(a.cont_numero,' ',a.cont_nombre) as contrato_id_name
				  FROM tbl_kpigral 
				  INNER JOIN tbl_contrato as a ON tbl_kpigral.contrato_id = a.contrato_id  ";
	}	

	public static function queryWhere(  ){

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');
	    if ($lintLevelUser==6){
		  return "  WHERE tbl_kpigral.id_kpi IS NOT NULL
		                AND EXISTS (select 1
					 	            from tbl_contrato
						            where tbl_contrato.contrato_id = tbl_kpigral.contrato_id
						            and tbl_contrato.entry_by_access = ".$lintIdUser." ) 
						      ";
		}else if ($lintLevelUser==4){
		  return "  WHERE tbl_kpigral.id_kpi IS NOT NULL
		                AND EXISTS (select 1
					 	            from tbl_contrato
						            where tbl_contrato.contrato_id = tbl_kpigral.contrato_id
						            and tbl_contrato.admin_id = ".$lintIdUser." )  ";
		}else{
			return "  WHERE tbl_kpigral.id_kpi IS NOT NULL ";
		}
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
