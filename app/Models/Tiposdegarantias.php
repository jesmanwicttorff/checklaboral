<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tiposdegarantias extends Sximo  {
	
	protected $table = 'tbl_tipos_de_garantias';
	protected $primaryKey = 'IdTipoGarantia';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_tipos_de_garantias.*, case when tbl_tipos_de_garantias.IdEstatus = 1 then 'Activo' else 'Inactivo' end as Estatus, case when tbl_tipos_de_garantias.SinMonto = 1 then 'Sin monto' else 'Con monto' end as Monto
FROM tbl_tipos_de_garantias ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tipos_de_garantias.IdTipoGarantia IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
