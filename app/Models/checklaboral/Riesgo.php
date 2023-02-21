<?php namespace App\Models\checklaboral;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class riesgo extends \App\Models\Sximo  {

	protected $table = 'tbl_riesgo';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();

	}

	public static function querySelect(  ){

		return "  SELECT answers.* FROM answers  ";
	}

	public static function queryWhere(  ){

		return "  WHERE answers.id IS NOT NULL ";
	}

	public static function queryGroup(){
		return "  ";
	}


}
