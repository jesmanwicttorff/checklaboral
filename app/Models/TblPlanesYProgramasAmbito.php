<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblplanesyprogramasambito extends Sximo  {
	
	protected $table = 'tbl_planes_y_programas_ambito';
	protected $primaryKey = 'ambito_id';

	public function scopeAmbito($query, $lintIdAmbito)
    {
        return $query->where('ambito_id', '=', $lintIdAmbito)->where("IdEstatus","=","1");
    }
    
	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_planes_y_programas_ambito.*,  
               CASE WHEN tbl_planes_y_programas_ambito.IdEstatus = 1 THEN 'Activo' ELSE 'Inactivo' END as IdEstatusDescripcion
FROM tbl_planes_y_programas_ambito  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_planes_y_programas_ambito.ambito_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
