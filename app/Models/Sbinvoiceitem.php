<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class sbinvoiceitem extends Sximo  {
	
	protected $table = 'sb_invoiceitems';
	protected $primaryKey = 'ItemID';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT sb_invoiceitems.* FROM sb_invoiceitems  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE sb_invoiceitems.ItemID IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
