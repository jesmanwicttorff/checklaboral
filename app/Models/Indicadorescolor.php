<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class indicadorescolor extends Sximo
{
    protected $table = 'tbl_indicadores_color';

    public function __construct() {
		parent::__construct();
		
    }
    
    public static function querySelect(  ){
		
		return "  SELECT tbl_indicadores_color.* FROM tbl_indicadores_color  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_indicadores_color.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
    }
    
}
