<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class obllabctrlmnt extends Sximo  {
	
	protected $table = 'tbl_obliglab_repo';
	protected $primaryKey = 'repo_id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_obliglab_repo.* FROM tbl_obliglab_repo  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_obliglab_repo.repo_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
