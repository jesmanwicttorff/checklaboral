<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Reportegeneraldetallecheck extends Sximo
{

    protected $table = 'tbl_reportes_generados_detalle';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_reportes_generados_detalle.* FROM tbl_reportes_generados_detalle  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_reportes_generados_detalle.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
    }
    
}
