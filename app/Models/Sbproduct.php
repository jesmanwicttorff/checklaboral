<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class sbproduct extends Sximo  {
	
	protected $table = 'sb_invoiceproducts';
	protected $primaryKey = 'ProductID';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT sb_invoiceproducts.* FROM sb_invoiceproducts  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE sb_invoiceproducts.ProductID IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
