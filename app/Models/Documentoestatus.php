<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class documentoestatus extends Sximo  {
	
	protected $table = 'tbl_documentos_estatus';
	protected $primaryKey = 'IdEstatus';

	public function __construct() {
		parent::__construct();
		
	}
	
}
