<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class f301 extends Sximo  {

	protected $table = 'tbl_f30_1';
	protected $primaryKey = 'IdF301';

	public function __construct() {
		parent::__construct();

	}

	public function Personas() {

		return $this->hasMany('App\Models\F301Personas','IdF301', 'IdF301');

	}

}
