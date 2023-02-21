<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class aprobadores extends Sximo  {
	
	protected $table = 'tbl_perfil_aprobacion';
	protected $primaryKey = 'IdGrupoAprobacion';

	public function __construct() {
		parent::__construct();
		
	}

}

?>