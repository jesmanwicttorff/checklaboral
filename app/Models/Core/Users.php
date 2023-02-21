<?php namespace App\Models\Core;

use App\Models\Sximo;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Users extends Sximo  {

	protected $table = 'tb_users';
	protected $primaryKey = 'id';
	protected $guarded = ['id'];

	public function __construct() {
		parent::__construct();
	}

	public function Groups(){
    return $this->belongsTo('App\Models\Core\Groups', 'group_id', 'id');
  }

	public function group(){
		return $this->belongsTo('App\Models\Core\Groups', 'group_id', 'group_id');
	}

	public static function querySelect(  ){
		return " 	SELECT  	tb_users.*,
								tb_groups.name
					FROM 		tb_users
					LEFT JOIN 	tb_groups ON tb_groups.group_id = tb_users.group_id ";
	}

	public static function queryWhere(){

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');
	    if ($lintLevelUser==6){
		  return " WHERE tb_users.id IS NOT NULL
		                AND tb_users.entry_by = ".$lintIdUser." ";
		}else if ($lintLevelUser==4){
		  return "  WHERE tb_users.id IS NOT NULL
		                AND tb_users.entry_by = ".$lintIdUser." ";
		}else{
			return "  WHERE tb_users.id IS NOT NULL ";
		}
	}

	public static function queryGroup(){
		return "      ";
	}

}
