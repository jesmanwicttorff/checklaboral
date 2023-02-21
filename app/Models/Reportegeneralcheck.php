<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Reportegeneralcheck extends Sximo
{

    protected $table = 'tbl_reportes_generados';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_reportes_generados.* FROM tbl_reportes_generados  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_reportes_generados.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
    }
    
}
