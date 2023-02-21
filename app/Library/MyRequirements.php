<?php namespace App\Library;

use App\Models\Requisito;
use App\Library\MyDocuments;
use App\Library\Acreditacion;
use App\Library\AcreditacionContrato;
use DateTime;

class MyRequirements extends MyContracts {

	private $garrEvents = array("FiniquitoContrato"=>2);

	public function __construct($pintIdContrato){
		parent::__construct($pintIdContrato);
	}

	public function getEvents(){
		return $garrEvents;
	}

	static public function getRequirements($pintIdEvent, $pintMensual = null){

		$lobjRequisitos = Requisito::where('IdEvento',$pintIdEvent)
		                             ->with(['Detalles','TipoDocumento']);

		if ($pintMensual){
			$lobjRequisitos = $lobjRequisitos->whereHas('TipoDocumento', function ($query) use ($pintMensual) {
			    $query->where('Periodicidad', '=', \DB::raw($pintMensual));
			});
		}

		$lobjRequisitos = $lobjRequisitos->get();

		if ($lobjRequisitos){
			return $lobjRequisitos;
		}

	}

	static public function Load($pintIdRequisito, $pintContratoId=null, $pintIdPersona = null, $pintCantidadMeses = 0 ) {


		$lintIdRequisito = $pintIdRequisito;
		$lobjRequisitos = Requisito::find($lintIdRequisito);
		$lintIdTipoDocumento = $lobjRequisitos->IdTipoDocumento;
		$ldatFechaReal = new DateTime(date('Y-m-d')); //fecha actual

		$fecha_actual = date('Y-m-d'); // obtengo el formato 2021-03 , año mes

		$lintPeriodicidad = $lobjRequisitos->TipoDocumento->Periodicidad;
		$lintAcreditacion = $lobjRequisitos->TipoDocumento->Acreditacion;
		$lintControllaboral = $lobjRequisitos->TipoDocumento->ControlCheckLaboral;
		$lbolTrimestral = false;
		$lbolSemestral = false;

		if ($lintPeriodicidad==1) {
			$lbolMensual = true;
		}else{
			$lbolMensual = false;
			if($lintPeriodicidad==2){
				$lbolTrimestral = true;
			}
			if($lintPeriodicidad==3){
				$lbolSemestral = true;
			}
		}

		$lobjGruposEspecificos = $lobjRequisitos->Detalles()->where('Entidad',1)->get();
		$larrGruposEspecificos = collect($lobjGruposEspecificos)->map(function($value){
								return $value->IdEntidad;
							})
							->toArray();

		$lobjServicios = $lobjRequisitos->Detalles()->where('Entidad',2)->get();
		$larrServicios = collect($lobjServicios)->map(function($value){
								return $value->IdEntidad;
							})
							->toArray();
		$lobjRoles = $lobjRequisitos->Detalles()->where('Entidad',3)->get();
		$larrRoles = collect($lobjRoles)->map(function($value){
								return $value->IdEntidad;
							})
							->toArray();


		if ($lobjRequisitos->Entidad=="1"){ //es contratista

			//Buscamos condiciones
			$lobjEntidades = \DB::table('tbl_contratistas')
								->select(\DB::raw('min(tbl_contrato.cont_fechaInicio) as FechaEmision'),
									   \DB::raw('max(tbl_contrato.cont_fechaFin) as FechaHasta'),
			                  	       'tbl_contratistas.IdContratista as IdEntidad',
			                  	       \DB::raw('null as contrato_id'),
			                  	       'tbl_contratistas.IdContratista as IdContratista',
			                  	       'tbl_contratistas.entry_by_access as entry_by_access',
			                  	       \DB::raw('max(tbl_contrato.acreditacion) as acreditacion'),
			                  	       \DB::raw('max(tbl_contrato.controllaboral) as controllaboral')
			                           )
			                    ->join('tbl_contrato',function ($table) {
			                    	$table->on('tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
			                    		  ->on('tbl_contrato.cont_estado','=',\DB::raw('1'));
			                    })
								->where('tbl_contrato.cont_fechaInicioContrato','<=',"$fecha_actual")
			                    ->groupBy('tbl_contratistas.IdContratista',
			                    	      'tbl_contrato.entry_by_access')
			                    ->distinct();

			if ($pintContratoId){
			    $lobjEntidades = $lobjEntidades->where('tbl_contrato.contrato_id','=',$pintContratoId);
			}

			if ($larrGruposEspecificos){
				$lobjEntidades = $lobjEntidades->join('tbl_contratos_servicios','tbl_contratos_servicios.id','=','tbl_contrato.idservicio')
											   ->whereIn('tbl_contratos_servicios.idgrupoespecifico',$larrGruposEspecificos);
			}

			if ($larrServicios){
				$lobjEntidades = $lobjEntidades->whereIn('tbl_contrato.IdServicio',$larrServicios);
			}

			if (!$lbolMensual and !$lbolSemestral and !$lbolTrimestral){
				$lobjEntidades = $lobjEntidades->whereNotExists(function ($query) use ($lintIdTipoDocumento){
							        $query->select(\DB::raw(1))
							              ->from('tbl_documentos')
							               ->whereraw('tbl_documentos.identidad = tbl_contrato.IdContratista')
									       ->whereraw('tbl_documentos.entidad = 1')
									       ->whereraw('tbl_documentos.idtipodocumento = '.$lintIdTipoDocumento)
									       ->whereraw('tbl_documentos.fechaemision = tbl_contrato.cont_fechaInicio - INTERVAL DAYOFMONTH(tbl_contrato.cont_fechaInicio) - 1 DAY');
							    });
			}


		}elseif ($lobjRequisitos->Entidad=="2"){

			//Buscamos condiciones
			$lobjEntidades = \DB::table('tbl_contrato')
								->select('tbl_contrato.cont_fechaInicio as FechaEmision',
									   'tbl_contrato.cont_fechaFin as FechaHasta',
			                  	       'tbl_contrato.contrato_id as IdEntidad',
			                  	       'tbl_contrato.contrato_id as contrato_id',
			                  	       'tbl_contrato.IdContratista as IdContratista',
			                  	       'tbl_contrato.entry_by_access as entry_by_access',
			                  	       'tbl_contrato.acreditacion',
			                  	       'tbl_contrato.controllaboral'
			                           )
								->distinct()
								->where('tbl_contrato.cont_fechaInicioContrato','<=',"$fecha_actual")
								->where('tbl_contrato.cont_estado','=',\DB::raw('1'));
								

			if ($pintContratoId){
			    $lobjEntidades = $lobjEntidades->where('tbl_contrato.contrato_id','=',$pintContratoId);
			}

			if ($larrGruposEspecificos){
				$lobjEntidades = $lobjEntidades->join('tbl_contratos_servicios','tbl_contratos_servicios.id','=','tbl_contrato.idservicio')
											   ->whereIn('tbl_contratos_servicios.idgrupoespecifico',$larrGruposEspecificos);
			}

			if ($larrServicios){
				$lobjEntidades = $lobjEntidades->whereIn('tbl_contrato.IdServicio',$larrServicios);
			}

			if (!$lbolMensual and !$lbolSemestral and !$lbolTrimestral){
				$lobjEntidades = $lobjEntidades->whereNotExists(function ($query) use ($lintIdTipoDocumento){
							        $query->select(\DB::raw(1))
							              ->from('tbl_documentos')
							               ->whereraw('tbl_documentos.identidad = tbl_contrato.contrato_id')
									       ->whereraw('tbl_documentos.entidad = 2')
									       ->whereraw('tbl_documentos.idtipodocumento = '.$lintIdTipoDocumento)
									       ->whereraw('tbl_documentos.fechaemision = tbl_contrato.cont_fechaInicio - INTERVAL DAYOFMONTH(tbl_contrato.cont_fechaInicio) - 1 DAY');
							    });
			}

		}elseif ($lobjRequisitos->Entidad=="3"){

			//Buscamos condiciones
			$lobjEntidades = \DB::table('tbl_contrato')
			                  ->select('tbl_contratos_personas.FechaInicioFaena as FechaEmision',
			                  	       'tbl_contrato.cont_fechaFin as FechaHasta',
			                  	       'tbl_contratos_personas.IdPersona as IdEntidad',
			                  	       'tbl_contratos_personas.contrato_id as contrato_id',
			                  	       'tbl_contratos_personas.IdContratista as IdContratista',
			                  	       'tbl_contratos_personas.entry_by_access as entry_by_access',
			                  	       'tbl_contratos_personas.acreditacion',
			                  	       'tbl_contratos_personas.controllaboral'
			                           )
			                  ->distinct()
			                  ->join('tbl_contratos_personas','tbl_contratos_personas.contrato_id','=','tbl_contrato.contrato_id')
							  ->where('tbl_contrato.cont_fechaInicioContrato','<=',"$fecha_actual")
			                  ->where('tbl_contrato.cont_estado','=',\DB::raw('1'));

			if ($pintContratoId){
				$lobjEntidades = $lobjEntidades->where('tbl_contratos_personas.contrato_id','=',$pintContratoId);
			}

			if ($pintIdPersona){
				$lobjEntidades = $lobjEntidades->where('tbl_contratos_personas.IdPersona','=',$pintIdPersona);
			}

			if ($larrGruposEspecificos){
				$lobjEntidades = $lobjEntidades->join('tbl_contratos_servicios','tbl_contratos_servicios.id','=','tbl_contrato.idservicio')
											   ->whereIn('tbl_contratos_servicios.idgrupoespecifico',$larrGruposEspecificos);
			}

			if ($larrServicios){
				$lobjEntidades = $lobjEntidades->whereIn('tbl_contrato.IdServicio',$larrServicios);
			}

			if ($larrRoles){
				$lobjEntidades = $lobjEntidades->whereIn('tbl_contratos_personas.IdRol',$larrRoles);
			}

			if (!$lbolMensual and !$lbolSemestral and !$lbolTrimestral){
				$lobjEntidades = $lobjEntidades->whereNotExists(function ($query) use ($lintIdTipoDocumento, $pintContratoId){
							        $query->select(\DB::raw(1))
							              ->from('tbl_documentos')
							               ->whereraw('tbl_documentos.identidad = tbl_contratos_personas.idpersona')
									       ->whereraw('tbl_documentos.entidad = 3')
										   ->whereraw('tbl_documentos.idtipodocumento = '.$lintIdTipoDocumento)
										   //->whereraw('tbl_documentos.contrato_id = '.$pintContratoId)
										   ->whereraw('tbl_documentos.fechaemision = tbl_contratos_personas.FechaInicioFaena - INTERVAL DAYOFMONTH(tbl_contratos_personas.FechaInicioFaena) - 1 DAY');
							    });
			}

		}elseif ($lobjRequisitos->Entidad=="10"){ //activos
			//Buscamos condiciones
			$lobjEntidades = \DB::table('tbl_activos_data')
			                  ->select('tbl_contrato.cont_fechaInicio as FechaEmision',
													   'tbl_contrato.cont_fechaFin as FechaHasta',
			                  	       'tbl_activos.IdActivo as IdEntidad',
																 'tbl_contrato.contrato_id as IdEntidad',
			                  	       'tbl_contrato.contrato_id as contrato_id',
			                  	       'tbl_contrato.IdContratista as IdContratista',
			                  	       'tbl_contrato.controllaboral',
			                  	       'tbl_activos.entry_by_access as entry_by_access',
			                  	       'tbl_activos.RequiereCertificacion as acreditacion')
												->join('tbl_activos','tbl_activos.IdActivo','=','tbl_activos_data.IdActivo')
												->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_activos_data.contrato_id')
			                  ->distinct()
							  ->where('tbl_contrato.cont_fechaInicioContrato','<=',"$fecha_actual");

			if (!$lbolMensual){
				$lobjEntidades = $lobjEntidades->whereNotExists(function ($query) use ($lintIdTipoDocumento){
							        $query->select(\DB::raw(1))
							              ->from('tbl_documentos')
							               ->whereraw('tbl_documentos.identidad = tbl_activos.IdActivo')
									       ->whereraw('tbl_documentos.entidad = 10')
									       ->whereraw('tbl_documentos.idtipodocumento = '.$lintIdTipoDocumento);
									      // ->whereraw('tbl_documentos.fechaemision = tbl_contratos.cont_fechaInicio - INTERVAL DAYOFMONTH(tbl_contratos_personas.FechaInicioFaena) - 1 DAY');
							    });
			}

			if ($larrGruposEspecificos){
				$lobjEntidades = $lobjEntidades->join('tbl_contratos_servicios','tbl_contratos_servicios.id','=','tbl_contrato.idservicio')
												 ->whereIn('tbl_contratos_servicios.idgrupoespecifico',$larrGruposEspecificos);
			}

			if ($larrServicios){
				$lobjEntidades = $lobjEntidades->whereIn('tbl_contrato.IdServicio',$larrServicios);
			}
			/*
			if ($larrRoles){
				$lobjEntidades = $lobjEntidades->whereIn('tbl_contratos_personas.IdRol',$larrRoles);
			}
			*/
		}

		$lobjEntidades = $lobjEntidades->get();

		foreach ($lobjEntidades as $larrEntidades) {

			$lintComienzo = 1;
			$lintCantidadMeses = 1;
			$ldatFechaEmision = $larrEntidades->FechaEmision;
			$ldatFechaHasta = $larrEntidades->FechaHasta;
			$lintAcreditacionEntidad = $larrEntidades->acreditacion;
			$lintControlLaboralEntidad = $larrEntidades->controllaboral;

			if (($lintAcreditacion&&$lintAcreditacionEntidad)||($lintControllaboral&&$lintControlLaboralEntidad)||(!$lintAcreditacion&&!$lintControllaboral)){
				$flag=true;
				if($lobjRequisitos->TipoDocumento->IdProceso==118){ //liquidacion de sueldo
					$trabDueno = \DB::table('tbl_contratos_personas')
					->join('tbl_contratistas','tbl_contratos_personas.IdContratista','=','tbl_contratistas.IdContratista')
					->join('tbl_personas','tbl_personas.IdPersona','=','tbl_contratos_personas.IdPersona')
					->where('tbl_personas.RUT','tbl_contratistas.RUT')
					->where('tbl_personas.IdPersona',$larrEntidades->IdEntidad)
					->first();
					if($trabDueno){
						$flag=false;
					}
				}

				if ($lbolMensual || $lbolTrimestral || $lbolSemestral){
					$ldatFechaEmision = new DateTime($ldatFechaEmision);
					$ldatFechaHasta = new DateTime($ldatFechaHasta);
					if ($ldatFechaHasta->format("Y-m") < $ldatFechaReal->format("Y-m")){
						$ldatInterval = $ldatFechaEmision->diff($ldatFechaHasta);
						$ldatFechaHasta = $ldatFechaHasta->format("Y-m").'-01';
						$lintComienzo = 0;
						$lintCantidadMeses = (int)($ldatInterval->format("%Y")* 12 + $ldatInterval->format("%m") );
					}else{
						$ldatInterval = $ldatFechaEmision->diff($ldatFechaReal);
						$ldatFechaHasta = $ldatFechaReal->format("Y-m").'-01';
						$lintCantidadMeses = (int)($ldatInterval->format("%Y")* 12 + $ldatInterval->format("%m") );
					}
					//Si recibimos como parametros los meses limitamos la creación
					if ($pintCantidadMeses>0){
						if ($lintCantidadMeses>$pintCantidadMeses){
							$lintCantidadMeses = $pintCantidadMeses;
						}
					}
	      }

	      for ($i=$lintComienzo; $i <= $lintCantidadMeses; $i++) {

	      	if ($lbolMensual){
	        	$ldatFechaEmision = date('Y-m-d',strtotime($ldatFechaHasta." -".$i." month"));
	        }

					if($flag){
						if(!$lbolMensual && !$lbolTrimestral && !$lbolSemestral){
							$lobjMyDocuments = new MyDocuments();
							$lobjMyDocuments::Save($lintIdTipoDocumento,
																	 $lobjRequisitos->Entidad,
																	 $larrEntidades->IdEntidad,
																	 null,
																	 $larrEntidades->IdContratista,
																	 $larrEntidades->contrato_id,
																	 $ldatFechaEmision);
						}
						if($lbolMensual){
						$lobjMyDocuments = new MyDocuments();
						$lobjMyDocuments::Save($lintIdTipoDocumento,
																 $lobjRequisitos->Entidad,
																 $larrEntidades->IdEntidad,
																 null,
																 $larrEntidades->IdContratista,
																 $larrEntidades->contrato_id,
																 $ldatFechaEmision);
						}
						if($lbolTrimestral){

							$ldatFechaEmision = date('Y-m-d',strtotime($ldatFechaHasta." -".$i." month"));
							$fecha = new DateTime($ldatFechaEmision);
							$fecha = $fecha->format('m');

							switch ($fecha) {
								case '02':
								case '05':
								case '08':
								case '11':{
									$fecha = new DateTime($ldatFechaEmision);
									$fecha = $fecha->format('m');
									$lobjMyDocuments = new MyDocuments();
									$lobjMyDocuments::Save($lintIdTipoDocumento,
																			 $lobjRequisitos->Entidad,
																			 $larrEntidades->IdEntidad,
																			 null,
																			 $larrEntidades->IdContratista,
																			 $larrEntidades->contrato_id,
																			 $ldatFechaEmision);
								}
								break;
								default:	break;
							}
						}
						if($lbolSemestral){

							$ldatFechaEmision = date('Y-m-d',strtotime($ldatFechaHasta." -".$i." month"));
							$fecha = new DateTime($ldatFechaEmision);
							$fecha = $fecha->format('m');
							switch ($fecha) {
								case '01':
								case '07':{
									$lobjMyDocuments = new MyDocuments();
									$lobjMyDocuments::Save($lintIdTipoDocumento,
																			 $lobjRequisitos->Entidad,
																			 $larrEntidades->IdEntidad,
																			 null,
																			 $larrEntidades->IdContratista,
																			 $larrEntidades->contrato_id,
																			 $ldatFechaEmision);
								}
								break;
								default:	break;
							}
						}
					}

				}

				if (!$pintContratoId && !$pintIdPersona){
					if ($lobjRequisitos->Entidad ==3 ){
						$lobjAcreditacion = new Acreditacion($larrEntidades->IdEntidad);
						$lobjAcreditacion::Accreditation();
					}
					if ($lobjRequisitos->Entidad == 2 ){
						$lobjAcreditacionContrato = new AcreditacionContrato($larrEntidades->IdEntidad);
						$lobjAcreditacionContrato::Accreditation();
					}
				}

			}

		}


	}


}

?>
