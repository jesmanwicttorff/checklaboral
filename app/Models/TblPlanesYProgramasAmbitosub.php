<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblplanesyprogramasambitosub extends Sximo  {
	
	protected $table = 'tbl_planes_y_programas_ambitosub';
	protected $primaryKey = 'subambito_id';

	public function scopeAmbito($query, $lintIdAmbito)
    {
        return $query->where('ambito_id', '=', $lintIdAmbito)->where("IdEstatus","=","1");
    }
    
	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_planes_y_programas_ambitosub.*,  
                         CASE WHEN tbl_planes_y_programas_ambitosub.IdEstatus = 1 THEN 
                             'Activo' ELSE 'Inactivo' 
                         END as IdEstatusDescripcion
                  FROM tbl_planes_y_programas_ambitosub ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_planes_y_programas_ambitosub.subambito_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
