<?php namespace App\Models\checklaboral;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Personasmaestro extends \App\Models\Sximo  {

	protected $table = 'tbl_personas_maestro';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();

	}

}
