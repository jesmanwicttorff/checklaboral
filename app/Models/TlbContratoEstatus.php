<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class TblContratoEstatus extends Sximo  {

	protected $table = 'tbl_contrato_estatus';

	public function __construct() {
		parent::__construct();

	}

}