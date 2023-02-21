<?php
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class contratosservicios extends Sximo  {

	protected $table = 'tbl_contratos_servicios';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();

	}

	protected $fillable = ['idgrupoespecifico', 'name'];

	public static function querySelect(  ){

		return "  SELECT tbl_contratos_servicios.*, tbl_contratos_grupos_especificos.name as idgrupoespecifico_desc
				  FROM tbl_contratos_servicios
				  LEFT JOIN tbl_contratos_grupos_especificos ON tbl_contratos_grupos_especificos.id = tbl_contratos_servicios.idgrupoespecifico ";
	}

	public static function queryWhere(  ){

		return "  WHERE tbl_contratos_servicios.id IS NOT NULL ";
	}

	public static function queryGroup(){
		return "  ";
	}


}
