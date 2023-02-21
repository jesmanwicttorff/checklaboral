<?php

namespace App\Library;

use \App\Models\CruceF301;
use \App\Models\F301;

class MyDocumentsF301 extends MyDocuments {

	public function __construct($pintIdDocumento)
  	{
    	parent::__construct($pintIdDocumento);
	}
	static public function Approve($IdAprobador=null) {

		$larrResult = self::Crossing();
		if ($larrResult['result']['IdEstatus']==3){
			//$larrResult = parent::Reject("Las personas del documento F30-1 no coinciden con las personas del maestro de personas");
			$larrResult = parent::Approve();
		}else{
			$larrResult = parent::Approve();
		}

		return $larrResult;

	}

	static public function Crossing($debug=0) {

		$lintIdDocumento = self::$gobjDocumento->IdDocumento;
		$lintContratoId = self::$gobjDocumento->contrato_id;
		$ldatFechaPeriodo = self::$gobjDocumento->FechaEmision;
		$lintIdEstatus = 5;

		$lobjF301 = F301::where('IdDocumento',$lintIdDocumento)->first();

		//ubicamos las personas que debían estar para el periodo
		$lobjPersonas = \DB::select("select tbl_personas.rut, tbl_personas.nombres, tbl_personas.apellidos, tbl_movimiento_personal.contrato_id, tbl_movimiento_personal.idpersona
									 from tbl_movimiento_personal
									 inner join tbl_personas on tbl_personas.IdPersona = tbl_movimiento_personal.IdPersona
									 left join (select *
												from tbl_movimiento_personal a
												where a.IdMovimientoPersonal in (select max(b.IdMovimientoPersonal)
																				 from tbl_movimiento_personal as b
																				 where a.contrato_id = b.contrato_id
																				 and a.idpersona = b.idpersona
																				 and b.FechaEfectiva <= LAST_DAY('".$ldatFechaPeriodo."')
																				 and b.IdAccion = 2)) c on tbl_movimiento_personal.contrato_id = c.contrato_id and tbl_movimiento_personal.idpersona = c.idpersona
									 where tbl_movimiento_personal.FechaEfectiva <= LAST_DAY('".$ldatFechaPeriodo."')
									 and tbl_movimiento_personal.IdAccion = 1
									 and tbl_movimiento_personal.FechaEfectiva != '0000-00-00'
									 and tbl_movimiento_personal.contrato_id = ".$lintContratoId."
									 and tbl_movimiento_personal.FechaEfectiva <= LAST_DAY('".$ldatFechaPeriodo."')
									 and IfNull(c.FechaEfectiva,LAST_DAY('".$ldatFechaPeriodo."')) >= '".$ldatFechaPeriodo."'
									 union
									 select tbl_personas.rut, tbl_personas.nombres, tbl_personas.apellidos, vw_finiquitos.contrato_id, vw_finiquitos.IdPersona
									 from tbl_personas
									 inner join vw_finiquitos on tbl_personas.IdPersona = vw_finiquitos.IdPersona
									 						 and vw_finiquitos.IdEstatus != 5
									 						 and vw_finiquitos.contrato_id = ".$lintContratoId."");

		//ubicamos las personas que vinieron en el F30-1
		$lobjPersonasF301 = $lobjF301->Personas()->get();

		$lobjCruceF301 = CruceF301::where("IdDocumento",$lintIdDocumento)->Delete();

		if ($lobjPersonas){

			foreach ($lobjPersonas as $larrPersona) {

				$lintExiste = 0;

				// buscamos dentro del arreglo de el F30-1
				foreach ($lobjPersonasF301 as $larrPersonasF301){
					if ($larrPersona->rut == $larrPersonasF301->RUT){
						$lintExiste = 1;
						break;
					}
				}

				if ($lintExiste == 0){
					$lobjCruceF301 = new CruceF301();
					$lobjCruceF301->iddocumento = $lintIdDocumento;
					$lobjCruceF301->periodo = $ldatFechaPeriodo;
					$lobjCruceF301->idpersona = $larrPersona->idpersona;
					$lobjCruceF301->rut = $larrPersona->rut;
					$lobjCruceF301->save();
					$lintIdEstatus = 3;
				}

			}

			//Verificamos las que están en el f30-1 y las que no están
			foreach ($lobjPersonasF301 as $larrPersonasF301) {

				// buscamos dentro del arreglo de el F30-1
				foreach ($lobjPersonas as $larrPersona){
					if ($larrPersona->rut == $larrPersonasF301->RUT){
						$lintExiste = 0;
					}
				}

				if ($lintExiste){
					$lobjCruceF301 = new CruceF301();
					$lobjCruceF301->iddocumento = $lintIdDocumento;
					$lobjCruceF301->periodo = $ldatFechaPeriodo;
					$lobjCruceF301->rut = $larrPersonasF301->RUT;
					$lobjCruceF301->save();
					$lintIdEstatus = 3;
				}

			}



		}else{

			// Todas las personas del F30-1 van a rechazo
			foreach ($lobjPersonasF301 as $larrPersonasF301) {
				$lobjCruceF301 = new CruceF301();
				$lobjCruceF301->iddocumento = $lintIdDocumento;
				$lobjCruceF301->periodo = $ldatFechaPeriodo;
				$lobjCruceF301->rut = $larrPersonasF301->RUT;
				$lobjCruceF301->save();
				$lintIdEstatus = 3;
			}

		}

		return array("sucess"=>true,"code"=>1,"result"=>["IdEstatus"=>$lintIdEstatus]);

	}


}
