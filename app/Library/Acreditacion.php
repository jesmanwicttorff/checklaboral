<?php namespace App\Library;

use App\Models\Personas;
use App\Models\Documentos;
use App\Models\Acreditacionpersona;
use App\Library\MyRequirements;
use MyPeoples;

class Acreditacion extends MyPeoples {

	public function __construct($pintIdPersona){
		parent::__construct($pintIdPersona);
	}

	static public function Accreditation($pintAcreditation = true){

		parent::$gobjPersona->load('Contratospersonas');
		$automatico = \Session::get('automatico');

		$lobjPersonas = parent::getDatos();
		$lintIdPersona = $lobjPersonas->IdPersona;
		$lobjDatosContratos = parent::getDatosContratos();
		$lintAccreditation = parent::$gobjPersona->Contratospersonas->acreditacion;
		if ($lintAccreditation){
			$lintAccreditation = "true";
		}else{
			$lintAccreditation = "false";
		}
		$lintControlLaboral = $lobjDatosContratos->controllaboral;
		$lintIdUser = parent::$gintIdUser;

		if ($lintAccreditation!=$pintAcreditation){

			if ($pintAcreditation=="true"){
				$lintAcreditacionPersona = 1;
			}else{
				$lintAcreditacionPersona = 0;
			}
			parent::$gobjPersona->Contratospersonas()->update(['acreditacion'=>$lintAcreditacionPersona]);

			if ($pintAcreditation=="true"){

				//levantamos los requisitos
				$lobjMyRequirements = new MyRequirements($lobjDatosContratos->contrato_id);
				// 3 = evento asignación de persona
	        	$lobjRequirements = $lobjMyRequirements::getRequirements(3);

	        	foreach ($lobjRequirements as $larrRequirements){
		          	if ( ( $larrRequirements->TipoDocumento->Acreditacion)){
		            	$lobjRequirements = $lobjMyRequirements::Load($larrRequirements->IdRequisito,
		            		                                          $lobjDatosContratos->contrato_id,
		            		                                          $lobjDatosContratos->IdPersona);
		          	}
	        	}

			}else{

				//Eliminamos los documentos relacionados con la acreditación
				\DB::table('tbl_documentos')
				     ->where('tbl_documentos.IdEntidad','=',$lintIdPersona)
				     ->where('tbl_documentos.Entidad','=',\DB::raw('3'))
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
			$flag=true;

			$lobjDocumentos = Documentos::where('tbl_documentos.IdEntidad',$lintIdPersona)
			                            ->where('tbl_documentos.Entidad',3)
			                            ->whereIn('tbl_documentos.IdEstatus',[1,2,3])
			                            ->where('tbl_documentos.contrato_id','=',parent::$gobjPersona->Contratospersonas->contrato_id)
			                            ->join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento", "=", "tbl_documentos.IdTipoDocumento")
			                            ->where('tbl_tipos_documentos.Acreditacion',1)
			                            ->get();

			$lobjAcreditracion = Acreditacionpersona::where('IdPersona',$lintIdPersona)
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
					if (parent::$gobjPersona->Contratospersonas->contrato_id!=$lobjAcreditracion->contrato_id){
						$lintCreacion = true;
					}else{
						$lintCreacion = false;
					}
					if ($lintAccreditationEstatus){
						$lobjAcreditracion->acreditacion = date('Y-m-d H:i:s');
					}else{
						$lobjAcreditracion->acreditacion = null;
					}
				}
			}else{
				$lintCreacion = true;
			}

			$docsVencidos = Documentos::join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento", "=", "tbl_documentos.IdTipoDocumento")
																	->where('tbl_documentos.IdEntidad',$lintIdPersona)
																	->where('tbl_documentos.Entidad',3)
																	->where('tbl_documentos.contrato_id','=',parent::$gobjPersona->Contratospersonas->contrato_id)
																	->where('tbl_tipos_documentos.Acreditacion',1)
																	->where('tbl_documentos.Vencimiento',1)
																	->where('tbl_documentos.IdEstatusDocumento',2)
																	->get();
			if(count($docsVencidos)){
				$lintCreacion = false;
				$flag=false;
			}

			//este codigo guarda relacion con el proceso que levanta solicitudes mensuales
			//y mantiene la fecha de acreditacion que tuviese la persona si ya estaba acreditado
			if(isset($automatico) and $automatico==1){
				$persona = Acreditacionpersona::where('IdPersona',$lintIdPersona)
				                                        ->where('IdEstatus',1)
				                                        ->first();
				if($persona){
					$lobjAcreditracion->acreditacion = $persona->acreditacion;
				}
			}

			if ($lintCreacion){
				Acreditacionpersona::where('IdPersona',$lintIdPersona)
			                                        ->where('IdEstatus',1)
			                                        ->update(['IdEstatus'=>2]);
				$lobjAcreditracion = new Acreditacionpersona();
				$lobjAcreditracion->IdPersona = $lintIdPersona;
				if ($lintAccreditationEstatus){
					$lobjAcreditracion->acreditacion = date('Y-m-d H:i:s');
				}
				$lobjAcreditracion->idcontratista = $lobjDatosContratos->IdContratista;
				$lobjAcreditracion->contrato_id = $lobjDatosContratos->contrato_id;
				$lobjAcreditracion->entry_by = $lintIdUser;
				$lobjAcreditracion->idestatus = 1;
			}

			if($flag){
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
			}else{
				$larrResultAcreditacion['IdEstatus'] = 0;
				$larrResultAcreditacion['Estatus'] = "Sin Acreditación";
				$larrResultAcreditacion['FechaAcreditacion'] = "";
				return array("status" => "success", "code"=>0,"message"=>"La persona ahora no se acreditará", "result"=>'');
			}

			return array("status" => "success", "code"=>0,"message"=>"La persona ahora se acreditará", "result"=>$larrResultAcreditacion);

		}else{

			return array("status" => "success", "code"=>0,"message"=>"La persona ahora no se acreditará", "result"=>'');
		}

	}

	static public function AssignContract($pintIdContrato, $pintIdPersona, $pintIdRol, $pintIdContratista=0, $pdatFechaInicioFaena = ''){

		$larrResult = array();
		$larrResult = parent::AssignContract($pintIdContrato, $pintIdPersona, $pintIdRol, $pintIdContratista, $pdatFechaInicioFaena);

		if ($larrResult['code']==1){
			self::Accreditation();
		}else{
			return $larrResult;
		}

		return $larrResult;
	}



}

?>
