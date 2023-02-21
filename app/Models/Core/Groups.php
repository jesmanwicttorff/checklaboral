<?php namespace App\Models\Core;

use App\Models\Sximo;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Groups extends Sximo  {
	
	protected $table = 'tb_groups';
	protected $primaryKey = 'group_id';

	public function __construct() {
		parent::__construct();
		
	}

	public function Users() {
		return $this->hasMany('App\Models\Core\Users', 'id', 'group_id');
	}

	public static function scopeContratistas($query) {
		return $query->whereIn('level',[6,15]);
	}

	public static function querySelect(  ){
		
		
		return " SELECT  
	tb_groups.group_id,
	tb_groups.name,
	tb_groups.description,
	tb_groups.level


FROM tb_groups  ";
	}

	public static function queryWhere(  ){
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');
	    if ($lintLevelUser==6){
		  return " WHERE tb_groups.group_id IS NOT NULL 
		                AND tb_groups.entry_by = ".$lintIdUser." ";
		}else if ($lintLevelUser==4){
		  return "  WHERE tb_groups.group_id IS NOT NULL 
		                AND tb_groups.entry_by = ".$lintIdUser." ";
		}else{
			return "  WHERE tb_groups.group_id IS NOT NULL ";
		}
		#return "  WHERE tb_groups.group_id IS NOT NULL    ";
	}
	
	public static function queryGroup(){
		return "    ";
	}
	

}
