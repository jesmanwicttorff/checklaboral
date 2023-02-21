<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tblfiscalizacioncontratista extends Sximo  {
	
	protected $table = 'tbl_fiscalización';
	protected $primaryKey = 'fisc_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_fiscalización.* FROM tbl_fiscalización  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_fiscalización.fisc_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
