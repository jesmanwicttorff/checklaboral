<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class f301personas extends Sximo  {
	
	protected $table = 'tbl_f30_1_empleados';
	protected $primaryKey = 'IdF301';

	public function __construct() {
		parent::__construct();
		
	}

	public function F301() {

		return $this->belongsTo('App\Models\F301','IdF301', 'IdF301');

	}

}
