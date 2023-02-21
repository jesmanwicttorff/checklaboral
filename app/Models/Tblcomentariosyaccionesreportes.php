<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblcomentariosyaccionesreportes extends Sximo  {
	
	protected $table = 'tbl_planes_y_programas';
	protected $primaryKey = 'programas_id';

	public function __construct() {
		parent::__construct();
		
	}
		
	public static function querySelect(  ){
		
				return " SELECT tbl_planes_y_programas.* FROM tbl_planes_y_programas  ";
	}	

	public static function queryWhere(  ){
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
		$lintIdUser = \Session::get('uid');
		
		if ($lintLevelUser==4){
			return " inner join tbl_contrato on tbl_planes_y_programas.contrato_id = tbl_contrato.contrato_id and tbl_planes_y_programas.entry_by_access = ".$lintIdUser."  ";
		} else {
			return "  WHERE tbl_planes_y_programas.programas_id IS NOT NULL ";
		}
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
