<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Entidades extends Sximo  {
	
	protected $table = 'tbl_entidades';
	protected $primaryKey = 'IdEntidad';

	public function __construct() {
		parent::__construct();
		
	}	

}
