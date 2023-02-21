<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class contratistas extends Sximo  {
	
	protected $table = 'tbl_contratistas';
	protected $primaryKey = 'IdContratista';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_contratistas.*,
		                 CONCAT(tb_users.first_name, ' ', tb_users.last_name) as entry_by_name
		          FROM tbl_contratistas
		          LEFT JOIN tb_users ON tbl_contratistas.entry_by = tb_users.id ";
	}

	public static function queryWhere(  ){
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');

	    if ($lintLevelUser==6 || $lintLevelUser==15){
	    	return "  WHERE tbl_contratistas.IdContratista IS NOT NULL 
            AND (tbl_contratistas.entry_by_access = ".$lintIdUser." 
             OR EXISTS (select tbl_contrato.contrato_id
		 	            from tbl_contrato
		 	            inner join tbl_contratos_subcontratistas on tbl_contratos_subcontratistas.IdSubContratista = tbl_contrato.IdContratista
		 	            where   tbl_contratos_subcontratistas.IdSubContratista = tbl_contratistas.IdContratista
			            and   tbl_contrato.entry_by_access = ".$lintIdUser.") 
			     ) ";
	    }elseif ($lintLevelUser==4){
		  return "  WHERE tbl_contratistas.IdContratista IS NOT NULL 
            AND ( EXISTS (select tbl_contrato.contrato_id
		 	            from tbl_contrato
		 	            where   tbl_contrato.IdContratista = tbl_contratistas.IdContratista
			            and   tbl_contrato.admin_id = ".$lintIdUser.") 
			       OR EXISTS (select tbl_contrato.contrato_id
		 	            from tbl_contrato
		 	            inner join tbl_contratos_subcontratistas on tbl_contratos_subcontratistas.IdSubContratista = tbl_contrato.IdContratista
		 	            where   tbl_contratos_subcontratistas.IdSubContratista = tbl_contratistas.IdContratista
			            and   tbl_contrato.admin_id = ".$lintIdUser.") 
			     ) ";
		}else{
		  return "  WHERE tbl_contratistas.IdContratista IS NOT NULL ";
		}
	}
	
	public static function queryGroup(){
		return "  ";
	}

	public function user(){

		return $this->hasOne('App\Models\Core\Users','id', 'entry_by_access');
	}

	public function contratos(){

		return $this->hasMany('App\Models\Contratos', 'IdContratista', 'IdContratista');
	}

	public function giro1(){

		return $this->hasOne('App\Models\Giros', 'IdGiro', 'IdGiro_1');
	}
	public function giro2(){

		return $this->hasOne('App\Models\Giros', 'IdGiro', 'IdGiro_2');
	}
	public function giro3(){

		return $this->hasOne('App\Models\Giros', 'IdGiro', 'IdGiro_3');
	}
	public function giro4(){

		return $this->hasOne('App\Models\Giros', 'IdGiro', 'IdGiro_4');
	}
	

}
