<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class acreditacion extends Sximo  {

	protected $table = 'tbl_contrato';

	public function __construct() {
		parent::__construct();

	}

}
