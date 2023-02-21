<?php namespace App\Models\checklaboral;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Diferenciancpersonas extends \App\Models\Sximo  {

	protected $table = 'tbl_diferencias_nc_personas';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();

	}

}
