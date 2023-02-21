<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class maetbgrupos extends Sximo  {
	
	protected $table = 'tb_groups';
	protected $primaryKey = 'group_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  
		        SELECT tb_groups.*, tb_groups_levels.name as levelname
		        FROM tb_groups
		        LEFT JOIN tb_groups_levels ON tb_groups_levels.id = tb_groups.level
		       ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tb_groups.group_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
