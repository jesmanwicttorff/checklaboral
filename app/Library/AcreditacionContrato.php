<?php namespace App\Library;

use App\Models\Personas;
use App\Models\Documentos;
use App\Models\Acreditacioncontrato as Contratosacreditacion;
use App\Library\MyContracts;
use App\Library\Acreditacion;
use App\Models\Contratospersonas;

class AcreditacionContrato extends MyContracts {

	public function __construct($pintIdContract){
		parent::__construct($pintIdContract);
	}

	static public function Accreditation($pintAcreditation = true, $pintAcreditationPeople = true){

		$lobjContratos = parent::getDatos();
		$lintIdContrato = $lobjContratos->contrato_id;
		$lintAccreditation = $lobjContratos->acreditacion;
		$lintControlLaboral = $lobjContratos->controllaboral;

		if ($lintAccreditation){
			$lintAccreditation = "true";
		}else{
			$lintAccreditation = "false";
		}
		$lintIdUser = parent::$gintIdUser;

		if ($lintAccreditation!=$pintAcreditation){
			if ($pintAcreditation=="true"){
				$lintAcreditacionContrato = 1;
			}else{
				$lintAcreditacionContrato = 0;
			}
			parent::$gobjContract->acreditacion = $lintAcreditacionContrato;
			parent::$gobjContract->save();

			if ($pintAcreditation=="true"){

				//levantamos los requisitos
				$lobjMyRequirements = new MyRequirements($lintIdContrato);
				// 2 = evento creacion de contrato
	        	$lobjRequirements = $lobjMyRequirements::getRequirements(2);

	        	foreach ($lobjRequirements as $larrRequirements){
		          	if ( ( $larrRequirements->TipoDocumento->Acreditacion)){
		            	$lobjRequirements = $lobjMyRequirements::Load($larrRequirements->IdRequisito,
		            		                                          $lintIdContrato);
		          	}
	        	}

			}else{

				if ($pintAcreditation=="false"&&$pintAcreditationPeople=="true"){

					$lobjContratosPersonas = Contratospersonas::where('contrato_id',$lintIdContrato)
					                                          ->get();

					foreach ($lobjContratosPersonas as $larrContratosPersonas) {

						$lobjAcreditacion = new Acreditacion($larrContratosPersonas->IdPersona);
						$lobjAcreditacion::Accreditation(false);

					}

				}

				//Eliminamos los documentos relacionados con la acreditación
				\DB::table('tbl_documentos')
				     ->where('tbl_documentos.IdEntidad','=',$lintIdContrato)
				     ->where('tbl_documentos.Entidad','=',\DB::raw('2'))
				     ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
				     ->where(function($query) use ($lintControlLaboral){
				     	$query->where(function($queryuno){
				     		$queryuno->whereraw('tbl_tipos_documentos.Acreditacion = 1')
				     		         ->whereraw('tbl_tipos_documentos.ControlCheckLaboral = 0');
				     	})
				     	->orwhere(function($querydos) use ($lintControlLaboral) {
				     		$querydos->whereraw('tbl_tipos_documentos.Acreditacion = 1')
				     		         ->whereraw('tbl_tipos_documentos.ControlCheckLaboral = 1')
				     		         ->whereraw($lintControlLaboral.' = 1');
				     	})
				     	->orwhere(function($querytres){
				     		$querytres->whereraw('tbl_tipos_documentos.BloqueaAcceso = 1');
				     	});
				     })
				     ->where('tbl_documentos.IdEstatus','!=',\DB::raw('5'))
				     ->update(['IdEstatus'=>"99"]); //verificar condición de eliminación.

			}
		}

		$lintAccreditation = $pintAcreditation;
		$lintCreacion = false;

		if ($lintAccreditation=="true"){

			$lintAccreditationEstatus = true;

			$lobjDocumentos = Documentos::where('tbl_documentos.IdEntidad',$lintIdContrato)
			                            ->where('tbl_documentos.Entidad',2)
			                            ->whereIn('tbl_documentos.IdEstatus',[1,2,3])
			                            ->join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento", "=", "tbl_documentos.IdTipoDocumento")
			                            ->where('tbl_tipos_documentos.Acreditacion',1)
			                            ->get();

			$lobjAcreditracion = Contratosacreditacion::where('contrato_id',$lintIdContrato)
			                                        ->where('IdEstatus',1)
			                                        ->first();

			if (count($lobjDocumentos)) {
				$lintAccreditationEstatus = false;
			}

			//
			if ($lobjAcreditracion){
				if (!$lobjAcreditracion->acreditacion){
					$lintCreacion = false;
					if ($lintAccreditationEstatus){
						$lobjAcreditracion->acreditacion = date('Y-m-d H:i:s');
					}
				}else{
					if ($lintAccreditationEstatus){
						$lobjAcreditracion->acreditacion = date('Y-m-d H:i:s');
					}else{
						$lobjAcreditracion->acreditacion = null;
					}
				}
			}else{
				$lintCreacion = true;
			}

			if ($lintCreacion){
				Contratosacreditacion::where('contrato_id',$lintIdContrato)
			                                        ->where('IdEstatus',1)
			                                        ->update(['IdEstatus'=>2]);
				$lobjAcreditracion = new Contratosacreditacion();
				$lobjAcreditracion->contrato_id = $lintIdContrato;
				if ($lintAccreditationEstatus){
					$lobjAcreditracion->acreditacion = date('Y-m-d H:i:s');
				}
				$lobjAcreditracion->contrato_id = $lintIdContrato;
				$lobjAcreditracion->entry_by = $lintIdUser;
				$lobjAcreditracion->idestatus = 1;
			}

			$lobjAcreditracion->save();

			if ($lobjAcreditracion->acreditacion!=""){
				$larrResultAcreditacion['IdEstatus'] = 1;
				$larrResultAcreditacion['Estatus'] = "Con Acreditación";
				$larrResultAcreditacion['FechaAcreditacion'] = \MyFormats::FormatDate($lobjAcreditracion->acreditacion);
			}else{
				$larrResultAcreditacion['IdEstatus'] = 0;
				$larrResultAcreditacion['Estatus'] = "Sin Acreditación";
				$larrResultAcreditacion['FechaAcreditacion'] = "";
			}

			return array("status" => "success", "code"=>0,"message"=>"El contrato ahora se acreditará", "result"=>$larrResultAcreditacion);

		}else{

			return array("status" => "success", "code"=>0,"message"=>"El contrato ahora no se acreditará", "result"=>'');
		}

	}

}
