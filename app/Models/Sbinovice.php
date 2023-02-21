<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class sbinovice extends Sximo  {
	
	protected $table = 'sb_invoices';
	protected $primaryKey = 'InvoiceID';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT sb_invoices.* FROM sb_invoices  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE sb_invoices.InvoiceID IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
