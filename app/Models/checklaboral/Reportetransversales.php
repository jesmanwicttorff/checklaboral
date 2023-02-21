<?php namespace App\Models\checklaboral;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class reportetransversales extends \App\Models\Sximo  {
	
	protected $table = 'answers';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

}
