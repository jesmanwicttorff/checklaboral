<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class reportespersonalizados extends Sximo  {
	
	protected $table = 'questions';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT questions.* FROM questions  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE questions.id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}

	public function reporteEmpresas(){

		$dataReporte = \DB::table('reporte_empresas');
		return $dataReporte;

	}
	public function reportePersonas(){

		$dataReporte = \DB::table('reporte_personas');
		return $dataReporte;

	}
	

}
