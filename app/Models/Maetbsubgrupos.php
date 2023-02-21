<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class maetbsubgrupos extends Sximo  {
	
	protected $table = 'tb_groups_sub';
	protected $primaryKey = 'subgroup_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tb_groups_sub.* FROM tb_groups_sub  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tb_groups_sub.subgroup_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
