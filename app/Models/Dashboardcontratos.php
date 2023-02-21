<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class dashboardcontratos extends Sximo  {

	protected $table = 'answers';
	protected $primaryKey = 'id';

	public function __construct() {
		parent::__construct();

	}

	public static function querySelect(  ){

		return "  SELECT answers.* FROM answers  ";
	}

	public static function queryWhere(  ){

		return "  WHERE answers.id IS NOT NULL ";
	}

	public static function queryGroup(){
		return "  ";
	}

	public static function getCantidad($id_ext,$tipo,$filtros=0){
		$cuenta=0;		
		if(isset($filtros[0]) and $filtros[0]==0){
			$cuenta =  \DB::select(\DB::raw("SELECT distinct(tbl_contrato.contrato_id)
				from `tbl_contrato`
				inner join `tbl_contratos_personas` on `tbl_contrato`.`contrato_id` = `tbl_contratos_personas`.`contrato_id`
				inner join `tbl_contrato_extension` on `tbl_contrato_extension`.`id_extension` = `tbl_contrato`.`id_extension`
				INNER JOIN tbl_contrato_tipogasto tg ON tg.id_tipogasto = tbl_contrato.id_tipogasto
				WHERE tbl_contrato_extension.id_extension =$id_ext and tg.id_tipogasto=$tipo"));
		}
		if(isset($filtros[1])){
			$cuenta =  \DB::select(\DB::raw("SELECT distinct(tbl_contrato.contrato_id)
				from `tbl_contrato`
				inner join `tbl_contratos_personas` on `tbl_contrato`.`contrato_id` = `tbl_contratos_personas`.`contrato_id`
				inner join `tbl_contrato_extension` on `tbl_contrato_extension`.`id_extension` = `tbl_contrato`.`id_extension`
				INNER JOIN tbl_contrato_tipogasto tg ON tg.id_tipogasto = tbl_contrato.id_tipogasto
				WHERE tbl_contrato_extension.id_extension =$id_ext and tg.id_tipogasto=$tipo and tbl_contrato.idcontratista=$filtros[1]"));
		}
		if(isset($filtros[2])){
			$cuenta =  \DB::select(\DB::raw("SELECT distinct(tbl_contrato.contrato_id)
				from `tbl_contrato`
				inner join `tbl_contratos_personas` on `tbl_contrato`.`contrato_id` = `tbl_contratos_personas`.`contrato_id`
				inner join `tbl_contrato_extension` on `tbl_contrato_extension`.`id_extension` = `tbl_contrato`.`id_extension`
				INNER JOIN tbl_contrato_tipogasto tg ON tg.id_tipogasto = tbl_contrato.id_tipogasto
				join tbl_contratos_centros on tbl_contratos_centros.contrato_id = tbl_contrato.contrato_id
				WHERE tbl_contrato_extension.id_extension =$id_ext and tg.id_tipogasto=$tipo and tbl_contratos_centros.IdCentro=$filtros[2]"));
		}
		if(isset($filtros[3])){
			$cuenta =  \DB::select(\DB::raw("SELECT distinct(tbl_contrato.contrato_id)
				from `tbl_contrato`
				inner join `tbl_contratos_personas` on `tbl_contrato`.`contrato_id` = `tbl_contratos_personas`.`contrato_id`
				inner join `tbl_contrato_extension` on `tbl_contrato_extension`.`id_extension` = `tbl_contrato`.`id_extension`
				INNER JOIN tbl_contrato_tipogasto tg ON tg.id_tipogasto = tbl_contrato.id_tipogasto
				join tbl_contratos_centros on tbl_contratos_centros.contrato_id = tbl_contrato.contrato_id
				WHERE tbl_contrato_extension.id_extension =$id_ext and tg.id_tipogasto=$tipo and tbl_contrato.contrato_id=$filtros[3]"));
		}

			return count($cuenta);
	}

	public static function getMontos($id_ext){
		$montos = \DB::table('tbl_contrato')->join('tbl_contratos_personas','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
		->join('tbl_contrato_extension','tbl_contrato_extension.id_extension','=','tbl_contrato.id_extension')
		->sum('cont_montoTotal');

		return $montos;
	}

}
