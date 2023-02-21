<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Accesos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Library\MyAccess;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use App\Models\Contratospersonas;
use App\Models\Contratos;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AccesosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'accesos';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Accesos();
		$this->modelview = new  \App\Models\Accesoareas();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'accesos',
			'pageUrl'			=>  url('accesos'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('accesos.index',$this->data);
	}

	public function postData( Request $request)
	{
		$this->data['setting']      = $this->info['setting'];
    $this->data['tableGrid']    = $this->info['config']['grid'];
    $this->data['access']       = $this->access;
		$this->data['sitio']				= \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
    return view('accesos.table',$this->data);
	}

	public function postInfoadicional(Request $request, $pintIdAcceso, $pintIdPersona = null){

		$data = array();
		$datamotivo = array();

		$lintIdEntidad = $pintIdPersona;

		$lobjAcceso = \DB::table('tbl_accesos')
		->select(\DB::raw('tbl_accesos.*'), "tb_users.first_name", "tb_users.last_name")
		->where('tbl_accesos.IdAcceso','=',$pintIdAcceso)
		->leftjoin('tb_users','tb_users.id','=','tbl_accesos.updated_by')
		->first();

		if ($lobjAcceso->IdTipoAcceso == 1 ){
			$lobjAccessLog = MyAccess::CheckAccessLog(1,0, $lintIdEntidad, null,  null, $pintIdAcceso); //$pintIdTipoEntidad, $pintIdTipoSubEntidad, $pintIdEntidad, $pintIdAreaTrabajo
		}else{
			$lobjAccessLog = MyAccess::CheckAccessLog(1,0, $pintIdAcceso, null, null,$pintIdAcceso); //$pintIdTipoEntidad, $pintIdTipoSubEntidad, $pintIdEntidad, $pintIdAreaTrabajo
		}

		$this->data['accesslog'] = $lobjAccessLog['data'];

		if ($lobjAcceso->IdTipoAcceso == 1 ){

			//Buscamos los documentos que le faltan a la persona
			$lobjPersonas = \DB::table('tbl_accesos')
			->where('tbl_accesos.IdAcceso',$pintIdAcceso)
			->join('tbl_documentos',function($table){
				$table->on('tbl_documentos.identidad','=','tbl_accesos.IdPersona')
				      ->on('tbl_documentos.contrato_id','=','tbl_accesos.contrato_id');
			})
	        ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
	        ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
	        ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
	        ->distinct()
	        ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
	            \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
	            \DB::raw('(CASE WHEN ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 AND tbl_tipos_documentos.Vigencia = 1 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
	        ->where('tbl_documentos.IdEntidad', '=', $lintIdEntidad)
	        ->where('tbl_documentos.Entidad', '=', "3")
	        ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
	        ->where(function($query) {
	            $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
	                  ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
	        })
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('tbl_documentos as doc ')
                    ->whereRaw('doc.IdDocumento = tbl_documentos.IdDocumentoRelacion')
                    ->Where(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "=", 1)
                    ->whereIn('doc.IdEstatus',[4,5]);
            })
	        ->orderBy("tbl_documentos.Entidad")
	        ->get();
	        foreach ($lobjPersonas as $larrPersonas) {
	            array_push($data, $larrPersonas);
	        }

	        //Buscamos los documentos que le faltan al contrato
	        $lobjContratos = \DB::table('tbl_documentos')
	        ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
	        ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
	        ->join('tbl_contratos_personas', 'tbl_contratos_personas.contrato_id', '=', 'tbl_documentos.IdEntidad')
	        ->join('tbl_personas', 'tbl_personas.IdPersona', '=', 'tbl_contratos_personas.IdPersona')
	        ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
	        ->distinct()
	        ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso', 'tbl_documentos.IdEstatus',
	            \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
	            \DB::raw('(CASE WHEN ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 AND tbl_tipos_documentos.Vigencia = 1 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
	        ->where('tbl_documentos.Entidad', '=', 2)
	        ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
	        ->where('tbl_personas.IdPersona', '=', $lintIdEntidad)
	        ->where(function($query) {
	            $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
	                  ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
	        })
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('tbl_documentos as doc ')
                    ->whereRaw('doc.IdDocumento = tbl_documentos.IdDocumentoRelacion')
                    ->Where(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "=", 1)
                    ->whereIn('doc.IdEstatus',[4,5]);
            })
	        ->orderBy("tbl_documentos.Entidad")
	        ->get();
	        foreach ($lobjContratos as $larrContratos) {
	            array_push($data, $larrContratos);
	        }

	        //Buscamos los documentos que le faltan al contratista
	        $lobjContratistas = \DB::table('tbl_documentos')
	        ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
	        ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
	        ->join('tbl_contratos_personas', 'tbl_contratos_personas.IdContratista', '=', 'tbl_documentos.IdEntidad')
	        ->join('tbl_personas', 'tbl_personas.IdPersona', '=', 'tbl_contratos_personas.IdPersona')
	        ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
	        ->distinct()
	        ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
	            \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
	            \DB::raw('(CASE WHEN ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 AND tbl_tipos_documentos.Vigencia = 1 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
	        ->where('tbl_documentos.Entidad', '=', 1)
	        ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
	        ->where('tbl_personas.IdPersona', '=', $lintIdEntidad)
	        ->where(function($query) {
	            $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
	                  ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
	        })
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('tbl_documentos as doc ')
                    ->whereRaw('doc.IdDocumento = tbl_documentos.IdDocumentoRelacion')
                    ->Where(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "=", 1)
                    ->whereIn('doc.IdEstatus',[4,5]);
            })
	        ->orderBy("tbl_documentos.Entidad")
	        ->get();
	        foreach ($lobjContratistas as $larrContratistas) {
	            array_push($data, $larrContratistas);
	        }

            //Buscamos los documentos que le faltan al subcontratista
            $lobjSubContratistas = \DB::table('tbl_documentos')
                ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
                ->join('tbl_contratos_personas', function ($join) {
                    $join->on('tbl_contratos_personas.contrato_id', '=', 'tbl_documentos.contrato_id')
                        ->on("tbl_contratos_personas.IdContratista", "=", "tbl_documentos.IdEntidad");
                })
                ->join('tbl_personas', 'tbl_personas.IdPersona', '=', 'tbl_contratos_personas.IdPersona')
                ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
                ->distinct()
                ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
                    \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
                    \DB::raw('(CASE WHEN tbl_documentos.FechaVencimiento < NOW() AND tbl_tipos_documentos.Vigencia = 1 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
                ->where('tbl_documentos.Entidad', '=', 9)
                ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
                ->where('tbl_personas.IdPersona', '=', $lintIdEntidad)
                ->where(function($query) {
                    $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
                        ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
                })
                ->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                        ->from('tbl_documentos as doc ')
                        ->whereRaw('doc.IdDocumento = tbl_documentos.IdDocumentoRelacion')
                        ->Where(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "=", 1)
                        ->whereIn('doc.IdEstatus',[4,5]);
                })
                ->orderBy("tbl_documentos.Entidad")
                ->get();
            foreach ($lobjSubContratistas as $larrSubContratistas) {
                array_push($data, $larrSubContratistas);
            }
	    }

	    if ($lobjAcceso->IdEstatusUsuario==2){ //buscar explicación
	    	if ($lobjAcceso->IdEstatus==1){
	    		$datamotivo[0] = array("Descripcion"=>"El usuario ".ucwords(strtolower($lobjAcceso->first_name)).' '.ucwords(strtolower($lobjAcceso->last_name)).' cambió su acceso el día '.\MyFormats::FormatDate($lobjAcceso->updatedOn) ) ;
	    	}
			if ($lobjAcceso->FechaFinal<date('Y-m-d')){
				$datamotivo[0] = array("Descripcion"=> "Acceso vencido el día ".\MyFormats::FormatDate($lobjAcceso->FechaFinal) ) ;
	    	}
	    }

        $this->data['motivonoaccesoestado'] = $datamotivo;

        $this->data['motivonoacceso'] = $data;

        $this->data['centros'] = \DB::table('tbl_centro')
		->select( \DB::raw("tbl_area_de_trabajo.IdAreaTrabajo as value"),
			      \DB::raw("tbl_centro.Descripcion as Centro"),
			      \DB::raw("tbl_area_de_trabajo.Descripcion as Area"),
				  \DB::raw("tbl_acceso_areas.IdAcceso as AreaAsignada"),
				  \DB::raw("concat(tb_users.First_Name, ' ', tb_users.Last_Name) as UsuarioCreacion")
			  )
		->join("tbl_area_de_trabajo", "tbl_centro.IdCentro", "=", "tbl_area_de_trabajo.IdCentro")
		->where("tbl_area_de_trabajo.IdTipoAcceso", "!=", 2)
		->orderby("tbl_centro.Descripcion","ASC")
		->orderby("tbl_area_de_trabajo.Descripcion","ASC")
		->join('tbl_acceso_areas', 'tbl_acceso_areas.IdAreaTrabajo', '=', 'tbl_area_de_trabajo.IdAreaTrabajo')
		->join('tbl_accesos', 'tbl_accesos.IdAcceso', '=', 'tbl_acceso_areas.IdAcceso')
		->leftjoin('tb_users', 'tb_users.id', '=', 'tbl_acceso_areas.entry_by')
		->where('tbl_accesos.IdAcceso','=', $pintIdAcceso)
		->get();

        return view('accesos.infoadicional',$this->data);
	}

	public function postEncuentrapersona(Request $request){

		$larrDatos = array();
		$lstrIdentificacion = $request->Identificacion;

		$lobjPersona = \DB::table('tbl_personas')
		->select("tbl_personas.rut", "tbl_personas.nombres", "tbl_personas.apellidos")
		->where('tbl_personas.rut','=',$lstrIdentificacion)
		->first();

		if ($lobjPersona){
			$larrDatos['data_rut'] = $lobjPersona->rut;
			$larrDatos['data_nombres'] = $lobjPersona->nombres;
			$larrDatos['data_apellidos'] = $lobjPersona->apellidos;
		}else{

			$lobjAcceso = \DB::table('tbl_accesos')
			->where('tbl_accesos.data_rut','=',$lstrIdentificacion)
			->first();

			if ($lobjAcceso) {
				$larrDatos['data_rut'] = $lobjAcceso->data_rut;
				$larrDatos['data_nombres'] = $lobjAcceso->data_nombres;
				$larrDatos['data_apellidos'] = $lobjAcceso->data_apellidos;
			}else{

				$larrDatos['data_rut'] = $lstrIdentificacion;
				$larrDatos['data_nombres'] = '';
				$larrDatos['data_apellidos'] = '';

			}

		}

		return response()->json(array(
				'status'=>'success',
				'data' => $larrDatos,
				'message'=> 'Consulta ejecutada satisfactiramente'
			));

	}

	public function getUsuarioAccesoAcreditado(Request $request){
		$rut = trim($request->rut);

		$lintIdPersona = \DB::table('tbl_personas')->where('RUT',$rut)->value('IdPersona');

		if($lintIdPersona>0){
			//vemos si tiene pase provisional
			$lintIdAcceso = \DB::table('tbl_accesos')->where('data_rut',$rut)->value('IdAcceso');
			$lintIdAreaTrabajo = \DB::table('tbl_acceso_areas')->where('IdAcceso',$lintIdAcceso)->value('IdAreaTrabajo');

			if($lintIdAcceso>0 && $lintIdAreaTrabajo>0)
			{

				$larrResult = MyAccess::CheckAccess(1, 0, str_replace('-','',$rut), $lintIdAreaTrabajo);

					//tiene acceso
				if($larrResult['code']==1)
				{
					if($larrResult['result']['Data']->IdTipoAcceso!=1) //2 y 3 visitas, provisional
						{
							$larrResult= array(
								'acreditacion'=>'1',
								'message'=> "Persona con pase visitas o provisional",
								'code' => '1'
							);
						}else{

						}
				}
				if($larrResult['code']==4){
					//pase vencido, vemos si está acreditado
					$lintIdPersona = \DB::table('tbl_personas')->where('RUT',$rut)->value('IdPersona');
					if($lintIdPersona>0){
						//vemos si la persona tiene la marca de acreditado
						$lobjContratoPersona = Contratospersonas::where('IdPersona',$lintIdPersona)->first();
						$lintContratoId = $lobjContratoPersona->contrato_id;

						//vemos si el contrato necesita Acreditacion
						$lobjContrato = Contratos::where('contrato_id',$lintContratoId)->first();
						if($lobjContrato->acreditacion==1){
							$acreditadoC = \DB::table('tbl_contratos_acreditacion')->where('contrato_id',$lintContratoId)->orderBy('id', 'desc')->select('idestatus','acreditacion')->first();
							if($acreditadoC){
								if($acreditadoC->idestatus==1 and !is_null($acreditadoC->acreditacion)){
									if($lobjContratoPersona->acreditacion==1){
										$acreditadoP = \DB::table('tbl_personas_acreditacion')->where('idpersona',$lintIdPersona)->where('contrato_id',$lintContratoId)->orderBy('id', 'desc')->select('idestatus','acreditacion')->first();
										if($acreditadoP->idestatus==1 and !is_null($acreditadoP->acreditacion)){
											$larrResult= array(
												'acreditacion'=>'1',
												'message'=> "Tiene acceso, está acreditado"
											);
										}else{
											$larrResult= array(
												'acreditacion'=>'0',
												'message'=> "No tiene acceso"
											);
										}
									}else{
										$larrResult= array(
											'acreditacion'=>'1',
											'message'=> "Tiene acceso, persona no necesita acreditarse"
										);
									}
								}else{
									$larrResult= array(
										'acreditacion'=>'0',
										'message'=> "No tiene acceso, ccto necesita acreditacion"
									);
								}
							}else{
								$larrResult= array(
									'acreditacion'=>'0',
									'message'=> "No tiene acceso, ccto necesita acreditacion"
								);
							}
						}else{
							$larrResult= array(
								'acreditacion'=>'1',
								'message'=> "Persona con acceso, ctto no necesita acreditacion"
							);
						}
					}else{
						$larrResult= array(
							'acreditacion'=>'0',
							'message'=> "Persona/activo sin acceso"
						);
					}

				}

			}else{
				//vemos si la persona tiene la marca de acreditado
				$lobjContratoPersona = Contratospersonas::where('IdPersona',$lintIdPersona)->first();
				$lintContratoId = $lobjContratoPersona->contrato_id;

				//vemos si el contrato necesita Acreditacion
				$lobjContrato = Contratos::where('contrato_id',$lintContratoId)->first();
				if($lobjContrato->acreditacion==1){
					$acreditadoC = \DB::table('tbl_contratos_acreditacion')->where('contrato_id',$lintContratoId)->orderBy('id', 'desc')->select('idestatus','acreditacion')->first();
					if($acreditadoC){
						if($acreditadoC->idestatus==1 and !is_null($acreditadoC->acreditacion)){
							if($lobjContratoPersona->acreditacion==1){
								$acreditadoP = \DB::table('tbl_personas_acreditacion')->where('idpersona',$lintIdPersona)->where('contrato_id',$lintContratoId)->orderBy('id', 'desc')->select('idestatus','acreditacion')->first();
								if($acreditadoP->idestatus==1 and !is_null($acreditadoP->acreditacion)){
									$larrResult= array(
										'acreditacion'=>'1',
										'message'=> "Tiene acceso, está acreditado"
									);
								}else{
									$larrResult= array(
										'acreditacion'=>'0',
										'message'=> "No tiene acceso"
									);
								}
							}else{
								$larrResult= array(
									'acreditacion'=>'1',
									'message'=> "Tiene acceso, persona no necesita acreditarse"
								);
							}
						}else{
							$larrResult= array(
								'acreditacion'=>'0',
								'message'=> "No tiene acceso, ccto necesita acreditacion"
							);
						}
					}else{
						$larrResult= array(
							'acreditacion'=>'0',
							'message'=> "No tiene acceso, ccto necesita acreditacion"
						);
					}
				}else{
					$larrResult= array(
						'acreditacion'=>'1',
						'message'=> "Persona con acceso, ctto no necesita acreditacion"
					);
				}
			}
			return response()->json($larrResult);
		}else{ //pases de visitas y activos
			//vemos a que area tiene acceso el fulano o activo
			$lintIdAcceso = \DB::table('tbl_accesos')->where('data_rut',$rut)->value('IdAcceso');
			$lintIdAreaTrabajo = \DB::table('tbl_acceso_areas')->where('IdAcceso',$lintIdAcceso)->value('IdAreaTrabajo');

			if($lintIdAcceso>0)
			{
				if($lintIdAreaTrabajo>0)
				{
					$larrResult = MyAccess::CheckAccess(1, 0, str_replace('-','',$rut), $lintIdAreaTrabajo);

						//tiene acceso
					if($larrResult['code']==1)
					{
						if($larrResult['result']['Data']->IdTipoAcceso!=1) //2 y 3 visitas, provisional
							{
								$larrResult= array(
									'acreditacion'=>'1',
									'message'=> "Persona con pase visitas o provisional",
									'code' => '1'
								);
							}else{

							}
					}
					if($larrResult['code']==4){
						//pase vencido, vemos si está acreditado
						$lintIdPersona = \DB::table('tbl_personas')->where('RUT',$rut)->value('IdPersona');
						if($lintIdPersona>0){
							//vemos si la persona tiene la marca de acreditado
							$lobjContratoPersona = Contratospersonas::where('IdPersona',$lintIdPersona)->first();
							$lintContratoId = $lobjContratoPersona->contrato_id;

							//vemos si el contrato necesita Acreditacion
							$lobjContrato = Contratos::where('contrato_id',$lintContratoId)->first();
							if($lobjContrato->acreditacion==1){
								$acreditadoC = \DB::table('tbl_contratos_acreditacion')->where('contrato_id',$lintContratoId)->orderBy('id', 'desc')->select('idestatus','acreditacion')->first();
								if($acreditadoC){
									if($acreditadoC->idestatus==1 and !is_null($acreditadoC->acreditacion)){
										if($lobjContratoPersona->acreditacion==1){
											$acreditadoP = \DB::table('tbl_personas_acreditacion')->where('idpersona',$lintIdPersona)->where('contrato_id',$lintContratoId)->orderBy('id', 'desc')->select('idestatus','acreditacion')->first();
											if($acreditadoP->idestatus==1 and !is_null($acreditadoP->acreditacion)){
												$larrResult= array(
													'acreditacion'=>'1',
													'message'=> "Tiene acceso, está acreditado"
												);
											}else{
												$larrResult= array(
													'acreditacion'=>'0',
													'message'=> "No tiene acceso"
												);
											}
										}else{
											$larrResult= array(
												'acreditacion'=>'1',
												'message'=> "Tiene acceso, persona no necesita acreditarse"
											);
										}
									}else{
										$larrResult= array(
											'acreditacion'=>'0',
											'message'=> "No tiene acceso, ccto necesita acreditacion"
										);
									}
								}else{
									$larrResult= array(
										'acreditacion'=>'0',
										'message'=> "No tiene acceso, ccto necesita acreditacion"
									);
								}
							}else{
								$larrResult= array(
									'acreditacion'=>'1',
									'message'=> "Persona con acceso, ctto no necesita acreditacion"
								);
							}
						}else{
							$larrResult= array(
								'acreditacion'=>'0',
								'message'=> "Persona/activo sin acceso"
							);
						}

					}
				}else{
						$larrResult= array(
							'status'=>'Error',
							'message'=> "Persona/activo sin area de trabajo"
						);
				}
			}else{

				$activos = \DB::table('tbl_activos_data_detalle')->where('Valor',$rut)->first();
				if($activos){

					$idactivodata = $activos->IdActivoData;
					$documentos = \DB::table('tbl_documentos_activos')->where('idactivodata',$idactivodata)->get();
					$flag=false;

					foreach ($documentos as $documento) {
						$doc = \DB::table('tbl_documentos')->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
								->where('IdDocumento',$documento->iddocumento)
								->where('tbl_tipos_documentos.Acreditacion',1)
								->first();
						if($doc)
						if($doc->IdEstatus!=5 or $doc->IdEstatusDocumento==2){
							$flag=true;
						}
					}

					if($flag){
						$larrResult= array(
							'acreditacion'=>'0',
							'message'=> "Activo sin acceso"
						);
					}else{
						$larrResult= array(
							'acreditacion'=>'1',
							'message'=> "Activo con acceso"
						);
					}
				}else{
					$larrResult= array(
						'acreditacion'=>'0',
						'message'=> "Persona/activo sin acceso"
					);
				}
			}
			return response()->json($larrResult);
		}

	}


	public function postDataAccesosFaena(Request $request){

		$dataDispositivo = $request->arraydata;

		$data = json_decode ($dataDispositivo);
		$contador=0;$fecha_marca='';$acceso='';

		if(count($data)>0)
		foreach ($data as $valor) {
			$rut = $valor->rut;
			$acceso = $valor->acceso;
			$fecha_marca = $valor->fecha_marca;
			$fechaCreacion = date('Y-m-d H:i:s');

			$existe = \DB::table('tbl_accesos_dispositivo')->where('rut',$rut)->where('acceso',$acceso)->where('fecha_marca',$fecha_marca)->count();
			if($existe<=0 && strlen($rut)<=20 ){
				\DB::table('tbl_accesos_dispositivo')->insert(["rut"=>$rut,"acceso"=>$acceso, "fecha_marca"=>$fecha_marca,"created_at"=>$fechaCreacion]);
			}
			$contador++;
		}

		if($contador>0){
			$larrResult= array(
				'recibidos'=>$contador,
				'status'=> "ok"
			);
		}else{
			$larrResult= array(
				'recibidos'=>0,
				'status'=> "error"
			);
		}


		return response()->json($larrResult);

	}

	public function getGeneraInformeAccesosDispositivo(Request $request){
		$hoy = date('Y-m-d H:i:s');
		$this->validate($request, [
    'dias' => 'required|numeric',
		]);
		$ndias = $request->dias;

		$datos = \DB::table('tbl_accesos_dispositivo')->whereBetween('fecha_marca',[date("Y-m-d", strtotime("$hoy -$ndias days")),$hoy ])->get();
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('Reporte Ingresos');
		$spreadsheet->setActiveSheetIndex(0);

		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('A1');

		$drawing->setWorksheet($spreadsheet->getActiveSheet());

		$spreadsheet->getActiveSheet()->getStyle('A1:G3')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setSize(14)->setBold(true);
		//$sheet->setCellValue('B1', 'REPORTE ACCESOS EECC OHL PARA MANTOS BLANCOS');
		$sheet->setCellValue('B1', 'REPORTE ACCESOS EECC');
		$sheet->setCellValue('B2', 'Rango de fechas consideradas');
		$sheet->setCellValue('D2', '['.date("Y-m-d", strtotime("$hoy -$ndias days")).']');
		$j=6;
		$spreadsheet->getActiveSheet()->getStyle('A6:I6')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(17);
		$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(17);
		$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(17);
		$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(25);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);
		//$spreadsheet->getActiveSheet()->setAutoFilter('A6:F6');
		$sheet->setCellValue('A'.$j, 'Empresa Contratista');
		$sheet->setCellValue('B'.$j, 'RUT/Identificador');
		$sheet->setCellValue('C'.$j, 'Nombres');
		$sheet->setCellValue('D'.$j, 'Apelllidos');
		$sheet->setCellValue('E'.$j, 'Estatus');
		$sheet->setCellValue('F'.$j, 'Tipo');
		$sheet->setCellValue('G'.$j, 'Fecha Marca');
		$sheet->setCellValue('H'.$j, 'Hora Marca');
		$sheet->setCellValue('I'.$j, 'Observación');
		$j=7;
		$procesadoe = array();
		$procesados = array();
		$flag=false;

		foreach ($datos as $dato) {

			$fecha_marca = new \DateTime($dato->fecha_marca);
			$hora_marca = new \DateTime($dato->fecha_marca);
			$fecha_marca = $fecha_marca->format('Y-m-d');
			$hora_marca = $hora_marca->format('HH:mm');
			$datoRut = preg_replace("/[^A-Za-z0-9\-,' ']/", '', $dato->rut);

			$persona_acceso = \DB::table('tbl_accesos_dispositivo')
				->where('acceso','entrada')
				->where('fecha_marca','like',$fecha_marca."%")
				->where('rut',$datoRut)
				->orderBy('id','asc')
				->first();

			$viejito=\DB::table('tbl_personas')
				->join('tbl_documentos','tbl_documentos.IdEntidad','=','tbl_personas.IdPersona')
				->join('tbl_documento_valor','tbl_documento_valor.IdDocumento','=','tbl_documentos.IdDocumento')
				->where('tbl_personas.rut',$datoRut)
				->where('tbl_documento_valor.idtipodocumentovalor',11)
				->select('tbl_documento_valor.valor')
				->first();

			if($persona_acceso){
					if(!in_array ($persona_acceso->rut."-".$fecha_marca,$procesadoe)){

						array_push($procesadoe,$persona_acceso->rut."-".$fecha_marca);

						$persona=\DB::table('tbl_personas')->where('rut',$persona_acceso->rut)->first();
						if(count($persona)>0){
							$acreditado = \DB::table('tbl_personas_acreditacion')->where('idpersona',$persona->IdPersona)->where('IdEstatus',1)->whereNotNull('Acreditacion')->first();
							$cctoAcreditado = Contratospersonas::where('IdPersona',$persona->IdPersona)->first();
							$flagCcto=true;
							if($cctoAcreditado){
								if($cctoAcreditado->acreditacion==1){
									if($acreditado){
										$ccto = \DB::table('tbl_contratos_acreditacion')->where('contrato_id',$acreditado->contrato_id)->where('IdEstatus',1)->whereNotNull('Acreditacion')->first();
										if($ccto){
											$flagCcto=true;
										}else{
											$flagCcto=false;
										}
									}
									else{
										$flagCcto=false;
									}
								}
							}else{
								$flagCcto=false;
							}

							if($acreditado && $flagCcto){
								$IdContratista = Contratospersonas::where('IdPersona',$persona->IdPersona)->value('IdContratista');
								$contratista = \DB::table('tbl_contratistas')->where('IdContratista',$IdContratista)->first();
								if($contratista){
									$sheet->setCellValue('A'.$j, strtoupper(strtolower($contratista->RazonSocial)));
								}else{
									$sheet->setCellValue('A'.$j, strtoupper(strtolower('S/I')));
								}
								$sheet->setCellValue('B'.$j, strtoupper(strtolower($persona->RUT)));
								$sheet->setCellValue('C'.$j, strtoupper(strtolower($persona->Nombres)));
								$sheet->setCellValue('D'.$j, strtoupper(strtolower($persona->Apellidos)));
								$sheet->setCellValue('E'.$j, strtoupper(strtolower($persona_acceso->acceso)));
								if(isset($viejito->valor))$sheet->setCellValue('F'.$j, strtoupper(strtolower($viejito->valor)));
								$fecha_marca = new \DateTime($persona_acceso->fecha_marca);
								$hora_marca = new \DateTime($persona_acceso->fecha_marca);
								$fecha_marca = $fecha_marca->format('d-m-Y');
								$hora_marca = $hora_marca->format('H:i:s');
								$sheet->setCellValue('G'.$j, $fecha_marca);
								$sheet->setCellValue('H'.$j, $hora_marca);
								$flag=true;
							}
						}else{
							$acceso = \DB::table('tbl_accesos')->where('data_rut',$persona_acceso->rut)->first();
							if($acceso){
								$lintIdAreaTrabajo = \DB::table('tbl_acceso_areas')->where('IdAcceso',$acceso->IdAcceso)->value('IdAreaTrabajo');
				        $larrResult = MyAccess::CheckAccess(1, 0, str_replace('-','',$persona_acceso->rut), $lintIdAreaTrabajo);
				        //tiene acceso
				        if($larrResult['code']==1)
				        {
			          	if($larrResult['result']['Data']->IdTipoAcceso!=1) //2 y 3 visitas, provisional
			            	{
                       $sheet->setCellValue('A'.$j, strtoupper(strtolower('Pase visita')));
                       $sheet->setCellValue('B'.$j, strtoupper(strtolower($persona_acceso->rut)));
                       $sheet->setCellValue('C'.$j, strtoupper(strtolower($acceso->data_nombres)));
                       $sheet->setCellValue('D'.$j, strtoupper(strtolower($acceso->data_apellidos)));
                       $sheet->setCellValue('E'.$j, strtoupper(strtolower($persona_acceso->acceso)));
											 $sheet->setCellValue('F'.$j, strtoupper(strtolower('Pase visita')));
                       $fecha_marca = new \DateTime($persona_acceso->fecha_marca);
                       $hora_marca = new \DateTime($persona_acceso->fecha_marca);
                       $fecha_marca = $fecha_marca->format('d-m-Y');
                       $hora_marca = $hora_marca->format('H:i:s');
                       $sheet->setCellValue('G'.$j, $fecha_marca);
                       $sheet->setCellValue('H'.$j, $hora_marca);
                       $sheet->setCellValue('I'.$j, $acceso->Observacion);
                       $flag=true;
               			}
				        }
							}else{
								$activos = \DB::table('tbl_activos_data_detalle')->where('Valor',$persona_acceso->rut)->first();
								if($activos){

									$idactivodata = $activos->IdActivoData;
									$documentos = \DB::table('tbl_documentos_activos')->where('idactivodata',$idactivodata)->get();
									$flag2=false;

									foreach ($documentos as $documento) {
										$doc = \DB::table('tbl_documentos')->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
												->where('IdDocumento',$documento->iddocumento)
												->where('tbl_tipos_documentos.Acreditacion',1)
												->first();
										if($doc)
										if($doc->IdEstatus!=5 or $doc->IdEstatusDocumento==2){
											$flag2=true;
										}
									}

									if($flag2){
										$larrResult= array(
											'acreditacion'=>'0',
											'message'=> "Activo sin acceso"
										);
									}else{
										$contrato = \DB::table('tbl_activos_data_detalle')->join('tbl_activos_data','tbl_activos_data_detalle.IdActivoData','=','tbl_activos_data.IdActivoData')
															->where('tbl_activos_data_detalle.Valor',$persona_acceso->rut)
															->first();
										$contratista = \DB::table('tbl_contratistas')->join('tbl_contrato','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
															->where('tbl_contrato.contrato_id',$contrato->contrato_id)->first();

										if($contratista){
											$sheet->setCellValue('A'.$j, strtoupper(strtolower($contratista->RazonSocial)));
										}else{
											$sheet->setCellValue('A'.$j, strtoupper(strtolower('S/I')));
										}
										$sheet->setCellValue('B'.$j, strtoupper(strtolower($persona_acceso->rut)));
										$sheet->setCellValue('C'.$j, '');
										$sheet->setCellValue('D'.$j, '');
										$sheet->setCellValue('E'.$j, strtoupper(strtolower($persona_acceso->acceso)));
										$sheet->setCellValue('F'.$j, strtoupper(strtolower('activo')));
										$fecha_marca = new \DateTime($persona_acceso->fecha_marca);
										$hora_marca = new \DateTime($persona_acceso->fecha_marca);
										$fecha_marca = $fecha_marca->format('d-m-Y');
										$hora_marca = $hora_marca->format('H:i:s');
										$sheet->setCellValue('G'.$j, $fecha_marca);
										$sheet->setCellValue('H'.$j, $hora_marca);
										//$sheet->setCellValue('I'.$j, $acceso->Observacion);
										$flag=true;
									}
								}
							}

						}

					if($flag)	{
						$j++;
					}
				}
			}
			$flag=false;
			$persona_acceso = \DB::table('tbl_accesos_dispositivo')->where('acceso','salida')->where('fecha_marca','like',$fecha_marca."%")->orderBy('id','desc')->where('rut',$dato->rut)->first();
			if($persona_acceso)
				{
					if(!in_array ($persona_acceso->rut."-".$fecha_marca,$procesados)){
					array_push($procesados,$persona_acceso->rut."-".$fecha_marca);
					$persona=\DB::table('tbl_personas')->where('rut',$persona_acceso->rut)->first();
					if(count($persona)>0){
						$acreditado = \DB::table('tbl_personas_acreditacion')->where('idpersona',$persona->IdPersona)->where('IdEstatus',1)->whereNotNull('Acreditacion')->first();
						$cctoAcreditado = Contratospersonas::where('IdPersona',$persona->IdPersona)->first();
						$flagCcto=true;
						if($cctoAcreditado){
							if($cctoAcreditado->acreditacion==1){
								if($acreditado){
									$ccto = \DB::table('tbl_contratos_acreditacion')->where('contrato_id',$acreditado->contrato_id)->where('IdEstatus',1)->whereNotNull('Acreditacion')->first();
									if($ccto){
										$flagCcto=true;
									}else{
										$flagCcto=false;
									}
								}
								else{
									$flagCcto=false;
								}
							}
						}else{
							$flagCcto=false;
						}
						if($acreditado && $flagCcto){
							$IdContratista = Contratospersonas::where('IdPersona',$persona->IdPersona)->value('IdContratista');
							$contratista = \DB::table('tbl_contratistas')->where('IdContratista',$IdContratista)->first();
							if($contratista){
								$sheet->setCellValue('A'.$j, strtoupper(strtolower($contratista->RazonSocial)));
							}else{
								$sheet->setCellValue('A'.$j, strtoupper(strtolower('S/I')));
							}
							$sheet->setCellValue('B'.$j, strtoupper(strtolower($persona->RUT)));
							$sheet->setCellValue('C'.$j, strtoupper(strtolower($persona->Nombres)));
							$sheet->setCellValue('D'.$j, strtoupper(strtolower($persona->Apellidos)));
							$sheet->setCellValue('E'.$j, strtoupper(strtolower($persona_acceso->acceso)));
							if(isset($viejito->valor))$sheet->setCellValue('F'.$j, strtoupper(strtolower($viejito->valor)));
							$fecha_marca = new \DateTime($persona_acceso->fecha_marca);
							$hora_marca = new \DateTime($persona_acceso->fecha_marca);
							$fecha_marca = $fecha_marca->format('d-m-Y');
							$hora_marca = $hora_marca->format('H:i:s');
							$sheet->setCellValue('G'.$j, $fecha_marca);
							$sheet->setCellValue('H'.$j, $hora_marca);
							$flag=true;
						}
					}else{
						$acceso = \DB::table('tbl_accesos')->where('data_rut',$persona_acceso->rut)->first();
						if($acceso){
							$sheet->setCellValue('A'.$j, strtoupper(strtolower('Pase visita')));
							$sheet->setCellValue('B'.$j, strtoupper(strtolower($persona_acceso->rut)));
							$sheet->setCellValue('C'.$j, strtoupper(strtolower($acceso->data_nombres)));
							$sheet->setCellValue('D'.$j, strtoupper(strtolower($acceso->data_apellidos)));
							$sheet->setCellValue('E'.$j, strtoupper(strtolower($persona_acceso->acceso)));
							$sheet->setCellValue('F'.$j, strtoupper(strtolower('Pase visita')));
							$fecha_marca = new \DateTime($persona_acceso->fecha_marca);
							$hora_marca = new \DateTime($persona_acceso->fecha_marca);
							$fecha_marca = $fecha_marca->format('d-m-Y');
							$hora_marca = $hora_marca->format('H:i:s');
							$sheet->setCellValue('G'.$j, $fecha_marca);
							$sheet->setCellValue('H'.$j, $hora_marca);
							$sheet->setCellValue('I'.$j, $acceso->Observacion);
							$flag=true;
						}else{
							$activos = \DB::table('tbl_activos_data_detalle')->where('Valor',$persona_acceso->rut)->first();
							if($activos){

								$idactivodata = $activos->IdActivoData;
								$documentos = \DB::table('tbl_documentos_activos')->where('idactivodata',$idactivodata)->get();
								$flag2=false;

								foreach ($documentos as $documento) {
									$doc = \DB::table('tbl_documentos')->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
											->where('IdDocumento',$documento->iddocumento)
											->where('tbl_tipos_documentos.Acreditacion',1)
											->first();
									if($doc)
									if($doc->IdEstatus!=5 or $doc->IdEstatusDocumento==2){
										$flag2=true;
									}
								}

								if($flag2){
									$larrResult= array(
										'acreditacion'=>'0',
										'message'=> "Activo sin acceso"
									);
								}else{
									$contrato = \DB::table('tbl_activos_data_detalle')->join('tbl_activos_data','tbl_activos_data_detalle.IdActivoData','=','tbl_activos_data.IdActivoData')
														->where('tbl_activos_data_detalle.Valor',$persona_acceso->rut)
														->first();
									$contratista = \DB::table('tbl_contratistas')->join('tbl_contrato','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
														->where('tbl_contrato.contrato_id',$contrato->contrato_id)->first();

									if($contratista){
										$sheet->setCellValue('A'.$j, strtoupper(strtolower($contratista->RazonSocial)));
									}else{
										$sheet->setCellValue('A'.$j, strtoupper(strtolower('s/i')));
									}
									$sheet->setCellValue('B'.$j, strtoupper(strtolower($persona_acceso->rut)));
									$sheet->setCellValue('C'.$j, '');
									$sheet->setCellValue('D'.$j, '');
									$sheet->setCellValue('E'.$j, strtoupper(strtolower($persona_acceso->acceso)));
									$sheet->setCellValue('F'.$j, strtoupper(strtolower('activo')));
									$fecha_marca = new \DateTime($persona_acceso->fecha_marca);
									$hora_marca = new \DateTime($persona_acceso->fecha_marca);
									$fecha_marca = $fecha_marca->format('d-m-Y');
									$hora_marca = $hora_marca->format('H:i:s');
									$sheet->setCellValue('G'.$j, $fecha_marca);
									$sheet->setCellValue('H'.$j, $hora_marca);
									//$sheet->setCellValue('I'.$j, $acceso->Observacion);
									$flag=true;
								}
							}
						}
					}
				}
				if($flag)	{
					$j++;
				}
			}
			$flag=false;
			$ultimafecha = $fecha_marca;
		}
		if(isset($ultimafecha)){
			$sheet->setCellValue('C2', '['.$ultimafecha.']');
		}else{
			$sheet->setCellValue('C2', '['.$hoy.']');
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Reporte_accesos.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;

	}


	public function getGeneraInformeDiario(Request $request, $email=null){
		$dia = $request->dia;
		if(isset($dia) and $dia!=date('Y-m-d')){
			$hoy = $dia;
			$hoyCorregido = new \DateTime($hoy);
			$hoyCorregido = $hoyCorregido->format('d-m-Y H:i');
		}else{
			$hoy = date('Y-m-d');
			$hoyCorregido = date('d-m-Y H:i');
		}

		$ultimoDato = \DB::table('tbl_accesos_dispositivo')->orderBy('id','desc')->first();
		if($ultimoDato){
			$ultimoDato = new \DateTime($ultimoDato->fecha_marca);
		}

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('REPORTE DIARIO');
		$spreadsheet->setActiveSheetIndex(0);

		$spreadsheet->getActiveSheet()->getStyle('C1')->getFont()->setSize(14)->setBold(true);
		//$sheet->setCellValue('C1', 'REPORTE DIARIO ACCESOS EECC OHL PARA MANTOS BLANCOS');
		$sheet->setCellValue('C1', 'REPORTE DIARIO ACCESOS EECC');
		$sheet->setCellValue('C2', 'FECHA:');
		$sheet->setCellValue('D2', $hoyCorregido);
		$sheet->setCellValue('E2', 'Último dato del día considerado:');
		if($ultimoDato){
			$sheet->setCellValue('F2', $ultimoDato->format('d-m-Y H:i'));
		}

		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('A1');

		$drawing->setWorksheet($spreadsheet->getActiveSheet());

		$spreadsheet->getActiveSheet()->getStyle('A7:I7')->getFont()->setBold(true);
		$styleArray = [
		    'borders' => [
		        'allBorders' => [
		            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
		            'color' => ['argb' => '00000000'],
		        ],
		    ],
		];

		$sheet->getStyle('D6:F6')->applyFromArray($styleArray);
		$sheet->mergeCells('D6:F6');
		$sheet->getStyle('D6:F6')->getAlignment()->setWrapText(true)->setHorizontal('center');
		$sheet->setCellValue('D6', '# TRAB. ACREDITADOS');

		$sheet->getStyle('G6:I6')->applyFromArray($styleArray);
		$sheet->mergeCells('G6:I6');
		$sheet->getStyle('G6:I6')->getAlignment()->setWrapText(true)->setHorizontal('center');
		$sheet->setCellValue('G6', '# INGRESOS');

		$sheet->getStyle('A7:I7')->applyFromArray($styleArray);
		$sheet->getStyle('C7:I7')->getAlignment()->setWrapText(true)->setHorizontal('center');
		$sheet->getColumnDimension('A')->setWidth(30);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setWidth(20);
		$sheet->getColumnDimension('D')->setAutoSize(true);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('I')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);
		$sheet->setCellValue('A7', 'EMPRESA');
		$sheet->setCellValue('B7', 'SERVICIO');
		$sheet->setCellValue('C7', '# TOTAL TRAB. PLATAFORMA');
		$sheet->setCellValue('D7', 'INDIRECTOS');
		$sheet->setCellValue('E7', 'DIRECTOS');
		$sheet->setCellValue('F7', 'TOTAL');
		$sheet->setCellValue('G7', 'INDIRECTOS');
		$sheet->setCellValue('H7', 'DIRECTOS');
		$sheet->setCellValue('I7', 'TOTAL');

		$contratistas = \DB::table('tbl_contratistas')
					->Join('tbl_contrato','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
					->where('tbl_contrato.cont_estado','!=',2)
					->where('tbl_contratistas.IdEstatus',1)
					->where('tbl_contratistas.IdContratista','>',1)
					->groupBy('tbl_contratistas.IdContratista')
					->get();
		$j=8;
		foreach ($contratistas as $contratista) {
			$personas = Contratospersonas::join('tbl_personas','tbl_personas.idPersona','=','tbl_contratos_personas.idPersona')
				->where('IdContratista',$contratista->IdContratista)
				->get();
			$acreditadosMoi = \DB::table('tbl_personas_acreditacion')
				->join('tbl_personas','tbl_personas_acreditacion.idpersona','=','tbl_personas.idpersona')
				->join('tbl_documentos','tbl_documentos.identidad','=','tbl_personas.IdPersona')
				->join('tbl_documento_valor','tbl_documento_valor.IdDocumento','=','tbl_documentos.IdDocumento')
				->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
				->where('tbl_personas_acreditacion.idestatus',1)
				->whereRaw('tbl_documentos.IdDocumento = tbl_contratos_personas.IdDocumento')
				->whereNotNull('tbl_personas_acreditacion.acreditacion')
				->where('tbl_personas_acreditacion.IdContratista',$contratista->IdContratista)
				->Where('tbl_documento_valor.valor', 'like', '%moi%')
				->count();
			$acreditadosMod = \DB::table('tbl_personas_acreditacion')
				->join('tbl_personas','tbl_personas_acreditacion.idpersona','=','tbl_personas.idpersona')
				->join('tbl_documentos','tbl_documentos.identidad','=','tbl_personas.IdPersona')
				->join('tbl_documento_valor','tbl_documento_valor.IdDocumento','=','tbl_documentos.IdDocumento')
				->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
				->where('tbl_personas_acreditacion.idestatus',1)
				->whereRaw('tbl_documentos.IdDocumento = tbl_contratos_personas.IdDocumento')
				->whereNotNull('tbl_personas_acreditacion.acreditacion')
				->where('tbl_personas_acreditacion.IdContratista',$contratista->IdContratista)
				->Where('tbl_documento_valor.valor', 'like', '%mod%')
				->count();
			$contadormoi=0;
			$contadormod=0;
			foreach ($personas as $persona) {
				$ingresosPersonamoi = 	\DB::table('tbl_personas_acreditacion')
					->join('tbl_personas','tbl_personas_acreditacion.idpersona','=','tbl_personas.idpersona')
					->join('tbl_documentos','tbl_documentos.identidad','=','tbl_personas.IdPersona')
					->join('tbl_documento_valor','tbl_documento_valor.IdDocumento','=','tbl_documentos.IdDocumento')
					->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
					->join('tbl_accesos_dispositivo','tbl_accesos_dispositivo.rut','=','tbl_personas.rut')
					->where('tbl_personas_acreditacion.idestatus',1)
					->whereRaw('tbl_documentos.IdDocumento = tbl_contratos_personas.IdDocumento')
					->whereNotNull('tbl_personas_acreditacion.acreditacion')
					->where('tbl_personas_acreditacion.IdContratista',$contratista->IdContratista)
				    ->Where('tbl_documento_valor.valor', 'like', '%moi%')
					->where('fecha_marca','like',$hoy."%")
					->where('acceso','entrada')
					->where('tbl_personas.idPersona',$persona->IdPersona)
					->first();

				$contadormoi = count($ingresosPersonamoi)+$contadormoi;
				$ingresosPersonamod = \DB::table('tbl_personas_acreditacion')
					->join('tbl_personas','tbl_personas_acreditacion.idpersona','=','tbl_personas.idpersona')
					->join('tbl_documentos','tbl_documentos.identidad','=','tbl_personas.IdPersona')
					->join('tbl_documento_valor','tbl_documento_valor.IdDocumento','=','tbl_documentos.IdDocumento')
					->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
					->join('tbl_accesos_dispositivo','tbl_accesos_dispositivo.rut','=','tbl_personas.rut')
					->where('tbl_personas_acreditacion.idestatus',1)
					->whereRaw('tbl_documentos.IdDocumento = tbl_contratos_personas.IdDocumento')
					->whereNotNull('tbl_personas_acreditacion.acreditacion')
					->where('tbl_personas_acreditacion.IdContratista',$contratista->IdContratista)
		     		->Where('tbl_documento_valor.valor', 'like', '%mod%')
					->where('fecha_marca','like',$hoy."%")
					->where('acceso','entrada')
					->where('tbl_personas.idPersona',$persona->IdPersona)
					->first();

				$contadormod = count($ingresosPersonamod)+$contadormod;
			}

			$sheet->setCellValue('A'.$j, strtoupper(strtolower($contratista->RazonSocial)));
			$sheet->setCellValue('B'.$j, 'Control Laboral');
			$sheet->setCellValue('C'.$j, count($personas));
			$sheet->setCellValue('D'.$j, $acreditadosMoi);
			$sheet->setCellValue('E'.$j, $acreditadosMod);
			$sheet->setCellValue('F'.$j, $acreditadosMoi+$acreditadosMod);

			$sheet->setCellValue('G'.$j, $contadormoi);
			$sheet->setCellValue('H'.$j, $contadormod);
			$sheet->setCellValue('I'.$j, $contadormoi+$contadormod);
			$j++;
		}
		if(!$email){
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename='."Reporte_accesos_diario_$hoy.xlsx");
			header('Cache-Control: max-age=0');

			$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
			$writer->save('php://output');
			exit;
		}else{

			$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
			$writer->save("storage/Reporte_accesos_diario_$hoy.xlsx");

		}

	}

	public function getGeneraInformeMensual(Request $request){

		$this->validate($request, [
    'mes' => 'bail|required|numeric',
    'ano' => 'required|numeric',
		]);

		if($request->mes!=date('m') or $request->ano!=date('Y')){
			$mesActual = $request->mes;
			$anoActual = $request->ano;
			$hoy = new \DateTime();
			$hoyCorregido = $hoy->format("31-$mesActual-$anoActual");
			$hoy = $hoy->format("$anoActual-$mesActual-31");
			$diaActual = 31;
		}else{
			$mesActual = date('m');
			$hoy = date('Y-m-d');
			$hoyCorregido = date('d-m-Y');
			$diaActual = date('d');
			$anoActual = date('Y');
		}

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('REPORTE MENSUAL');
		$spreadsheet->setActiveSheetIndex(0);

		$spreadsheet->getActiveSheet()->getStyle('C1')->getFont()->setSize(14)->setBold(true);
		//$sheet->setCellValue('C1', 'REPORTE MENSUAL ACCESOS EECC OHL PARA MANTOS BLANCOS');
		$sheet->setCellValue('C1', 'REPORTE MENSUAL ACCESOS EECC');
		$sheet->setCellValue('C2', 'PERIODO:');
		$sheet->setCellValue('D2', "01-$mesActual-$anoActual");
		$sheet->setCellValue('E2', 'AL');
		$sheet->setCellValue('F2', $hoyCorregido);

		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('A1');

		$drawing->setWorksheet($spreadsheet->getActiveSheet());

		$spreadsheet->getActiveSheet()->getStyle('A7:AM7')->getFont()->setBold(true);
		$styleArray = [
				'borders' => [
						'allBorders' => [
								'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
								'color' => ['argb' => '00000000'],
						],
				],
		];

		$sheet->getStyle('A7:AM7')->applyFromArray($styleArray);
		$sheet->getStyle('C7:I7')->getAlignment()->setWrapText(true)->setHorizontal('center');
		$sheet->getColumnDimension('A')->setWidth(30);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setWidth(20);
		$sheet->getColumnDimension('D')->setAutoSize(true);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('I')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);
		$sheet->getColumnDimension('AL')->setAutoSize(true);
		$sheet->getColumnDimension('AM')->setAutoSize(true);
		$sheet->setCellValue('A7', 'EMPRESA');
		$sheet->setCellValue('B7', 'SERVICIO');
		$sheet->setCellValue('C7', 'RUT');
		$sheet->setCellValue('D7', 'NOMBRE');
		$sheet->setCellValue('E7', 'APELLIDOS');
		$sheet->setCellValue('F7', 'ROL');
		$sheet->setCellValue('AL7', '# DIAS TRABAJADOS');
		$sheet->setCellValue('AM7', '# DIAS DE DESCANSO');

		$k=7;
		$i=1;
		for($k=7;$k<=37;$k++){
			switch ($mesActual) {
				case '01':
						$mesActualcorregido="Ene";
					break;
				case '02':
						$mesActualcorregido="Feb";
					break;
				case '03':
						$mesActualcorregido="Mar";
					break;
				case '04':
						$mesActualcorregido="Abr";
					break;
				case '05':
						$mesActualcorregido="May";
					break;
				case '06':
						$mesActualcorregido="Jun";
					break;
				case '07':
						$mesActualcorregido="Jul";
					break;
				case '08':
						$mesActualcorregido="Ago";
					break;
				case '09':
						$mesActualcorregido="Sep";
					break;
				case '10':
						$mesActualcorregido="Oct";
					break;
				case '11':
						$mesActualcorregido="Nov";
					break;
				case '12':
						$mesActualcorregido="Dic";
					break;
				default:
					// code...
					break;
			}
			$sheet->setCellValueByColumnAndRow($k,7,$i."-".$mesActualcorregido); $i++;
		}

		$contratistas = \DB::table('tbl_contratistas')->where('IdEstatus',1)->where('IdContratista','>',1)->get();
		$j=8;
		foreach ($contratistas as $contratista) {
			$personas = Contratospersonas::join('tbl_personas','tbl_personas.idPersona','=','tbl_contratos_personas.idPersona')
				->join('tbl_roles','tbl_roles.IdRol','=','tbl_contratos_personas.IdRol')
				->where('IdContratista',$contratista->IdContratista)
				->select('Nombres','Apellidos','Descripción','RUT')
				->get();
			$k=7;
			$i=1;
			$cuenta=0;
			$contador=0;
			foreach ($personas as $persona) {
				$sheet->setCellValue('A'.$j, strtoupper(strtolower($contratista->RazonSocial)));
				$sheet->setCellValue('B'.$j, 'Control Laboral');
				$sheet->setCellValue('C'.$j, $persona->RUT);
				$sheet->setCellValue('D'.$j, strtoupper($persona->Nombres));
				$sheet->setCellValue('E'.$j, strtoupper($persona->Apellidos));
				$sheet->setCellValue('F'.$j, strtoupper($persona->Descripción));
				for($k;$k<$diaActual+7;$k++){
					if($i<=31){
						$fecha = new \DateTime($anoActual."-".$mesActual."-".$i);
						$fecha = $fecha->format('Y-m-d');
						$cuenta = self::checkacceso($persona->RUT, $fecha);
						$sheet->setCellValueByColumnAndRow($k,$j,$cuenta);
						$contador = $cuenta+$contador;
					}
					$i++;
				}
				$sheet->setCellValue('AL'.$j, $contador);
				$sheet->setCellValue('Am'.$j, 31-$contador);
				$j++;
				$contador=0;
				$k=7;
				$i=1;
			}

		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Reporte_accesos_Mensual.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;
	}

	public function getGeneraInformeAccesos(Request $request){

		$this->validate($request, [
    'mesi' => 'bail|required|numeric',
    'anoi' => 'required|numeric',
		'mesf' => 'bail|required|numeric',
    'anof' => 'required|numeric'
		]);

		$anoi = $request->anoi;
		$anof = $request->anof;
		$mesi = $request->mesi;
		$mesf = $request->mesf;

		if($request->anof < $request->anoi){
			return response()->json(array(
					'msg'=>"año final no puede ser inferior al inicial",
					'status'=> "error"
				));
		}

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('REPORTE PASES');
		$spreadsheet->setActiveSheetIndex(0);

		$spreadsheet->getActiveSheet()->getStyle('C1')->getFont()->setSize(14)->setBold(true);
		$sheet->setCellValue('C1', 'REPORTE DE PASES');

		$sheet->setCellValue('C2', 'PERIODO DESCARGADO:');
		$sheet->setCellValue('D2', "01-$mesi-$anoi");
		$sheet->setCellValue('E2', 'AL');
		$sheet->setCellValue('F2', "31-$mesf-$anof");

		$sheet->setCellValue('C3', 'FECHA DESCARGA INFORME:');
		$sheet->setCellValue('D3',  date('d-m-Y H:i'));

		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('A1');

		$drawing->setWorksheet($spreadsheet->getActiveSheet());

		$spreadsheet->getActiveSheet()->getStyle('A7:K7')->getFont()->setBold(true);
		$styleArray = [
				'borders' => [
						'allBorders' => [
								'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
								'color' => ['argb' => '00000000'],
						],
				],
		];

		$sheet->getStyle('A7:K7')->applyFromArray($styleArray);
		$sheet->getStyle('C7:K7')->getAlignment()->setWrapText(true)->setHorizontal('center');
		$sheet->getColumnDimension('A')->setWidth(30);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setWidth(20);
		$sheet->getColumnDimension('D')->setAutoSize(true);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);
		$sheet->getColumnDimension('K')->setAutoSize(true);
		$sheet->setCellValue('A7', 'Tipo de acceso');
		$sheet->setCellValue('B7', 'Fecha inicio');
		$sheet->setCellValue('C7', 'Fecha fin');
		$sheet->setCellValue('D7', 'Identificador');
		$sheet->setCellValue('E7', 'Nombres');
		$sheet->setCellValue('F7', 'Apellidos');
		$sheet->setCellValue('G7', 'Mail ');
		$sheet->setCellValue('H7', 'Observación');
		$sheet->setCellValue('I7', 'Uen');
		$sheet->setCellValue('J7', 'Areas');
		$sheet->setCellValue('K7', 'Autorizado por');

		$accesos = \DB::table('tbl_accesos')
			->leftJoin('tb_users','tbl_accesos.entry_by','=','tb_users.id')
			->where('tbl_accesos.createdOn','>=',"$anoi-$mesi-01")
			->where('tbl_accesos.createdOn','<=',"$anof-$mesf-31")
			->get();

		$j=8;
		$centro=[];
		$uen=[];
		$centroConcatenado='';
		$uenConcatenado='';

		if($accesos)
		foreach ($accesos as $acceso) {
			switch ($acceso->IdTipoAcceso) {
				case '1':
						$tipoacceso="Emergencia";
					break;
				case '2':
						$tipoacceso="Permiso de trabajo especial";
					break;
				case '3':
						$tipoacceso="Provisional";
					break;
				case '4':
						$tipoacceso="Provisional Conductor";
					break;
				default:
					// code...
					break;
			}
			$uensyct = \DB::table('tbl_acceso_areas')
				->join('tbl_uen_ct','tbl_uen_ct.uenct_id','=','tbl_acceso_areas.IdAreaTrabajo')
				->join('tbl_centro_trabajo','tbl_centro_trabajo.ct_id','=','tbl_uen_ct.ct_id')
				->join('tbl_centro','tbl_centro.IdCentro','=','tbl_uen_ct.uen_id')
				->where('tbl_acceso_areas.IdAcceso',$acceso->IdAcceso)
				->get();
			if($uensyct){
				foreach ($uensyct as $dato) {
					array_push($centro,$dato->descripcion);
					array_push($uen,$dato->Descripcion);
				}
			}
			$centroConcatenado = implode(",", $centro);
			$uenConcatenado = implode(",", $uen);
			$sheet->setCellValue('A'.$j, strtoupper(strtolower($tipoacceso)));
			$sheet->setCellValue('B'.$j, strtoupper(strtolower($acceso->FechaInicio)));
			$sheet->setCellValue('C'.$j, strtoupper(strtolower($acceso->FechaFinal)));
			$sheet->setCellValue('D'.$j, strtoupper(strtolower($acceso->data_rut)));
			$sheet->setCellValue('E'.$j, strtoupper(strtolower($acceso->data_nombres)));
			$sheet->setCellValue('F'.$j, strtoupper(strtolower($acceso->data_apellidos)));
			$sheet->setCellValue('G'.$j, strtoupper(strtolower($acceso->email)));
			$sheet->setCellValue('H'.$j, strtoupper(strtolower($acceso->Observacion)));
			$sheet->setCellValue('I'.$j, strtoupper(strtolower($uenConcatenado)));
			$sheet->setCellValue('J'.$j, strtoupper(strtolower($centroConcatenado)));
			$sheet->setCellValue('K'.$j, strtoupper(strtolower($acceso->first_name." ".$acceso->last_name)));
			$j++;
			$centroConcatenado='';
			$uenConcatenado='';
			$centro=[];
			$uen=[];
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Reporte_pases.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;
	}

	public function sendInformeEmail(){

		$request = new \Illuminate\Http\Request();
		$request->dia = date('Y-m-d');
		$hoy = date('Y-m-d');

		self::getGeneraInformeDiario($request, 1);
		$excel = base_path("storage/Reporte_accesos_diario_$hoy.xlsx");

		//\Log::info("enviamos correo informe diario accesos ohl");
		\Mail::send('emails.informeacceso',['excel'=>$excel], function ($m) use ($excel){
			$m->from(CNF_EMAIL);
			$destinatarios = ['consultas.ohl@sourcing.cl'];
			$m->to(CNF_EMAIL);
			$m->subject("Reporte diario de accesos OHL");
			$m->bcc(['angel.montt@ohlichile.com','julio.platas@ohl.es', 'jorge.tapia@ohlichile.com']);
			$m->attach($excel);
 		});

	}

	public function sendDocsVencidosEmail(){
	  $hoy=date('Y-m-d');
	  $hoyCorregido = date('d-m-Y');

		$contratistasAInformar = \DB::table('tbl_contratistas')
			->join('tb_users','tbl_contratistas.entry_by_access','=','tb_users.id')
			->join('tbl_contrato','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
			->select('tbl_contratistas.IdContratista','tb_users.email')
			->where('tbl_contrato.cont_estado','!=','2')
			->distinct()
			->get();

		foreach ($contratistasAInformar as $ct) {
			$docsVencidos = \DB::table('tbl_documentos')
		    ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
		    ->join('tbl_entidades','tbl_documentos.Entidad','=','tbl_entidades.IdEntidad')
				->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_documentos.IdContratista')
		    ->select('tbl_tipos_documentos.Descripcion','tbl_documentos.FechaVencimiento','tbl_entidades.Entidad as Enti','tbl_tipos_documentos.Entidad', 'tbl_documentos.IdEntidad','tbl_contratistas.RazonSocial')
		    ->where('Vencimiento',1)->where('IdEstatusDocumento',2)->where('tbl_contratistas.IdContratista',$ct->IdContratista)->orderBy('tbl_documentos.FechaVencimiento','desc')->get();

		  $docsXvencer  = \DB::table('tbl_documentos')
		    ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
		    ->join('tbl_entidades','tbl_documentos.Entidad','=','tbl_entidades.IdEntidad')
				->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_documentos.IdContratista')
		    ->select('tbl_tipos_documentos.Descripcion','tbl_documentos.FechaVencimiento','tbl_entidades.Entidad as Enti','tbl_tipos_documentos.Entidad', 'tbl_documentos.IdEntidad','tbl_contratistas.RazonSocial')
		    ->where('Vencimiento',1)->whereBetween('FechaVencimiento',[ $hoy ,date("Y-m-d",strtotime("$hoy +30 days")) ])
				->where('IdEstatusDocumento','=',null)->where('tbl_contratistas.IdContratista',$ct->IdContratista)->orderBy('tbl_documentos.FechaVencimiento','asc')->get();

		  $spreadsheet = new Spreadsheet();
		  $sheet = $spreadsheet->getActiveSheet();
		  $spreadsheet->getActiveSheet()->setTitle('REPORTE');
		  $spreadsheet->setActiveSheetIndex(0);

		  $spreadsheet->getActiveSheet()->getStyle('C1')->getFont()->setSize(14)->setBold(true);
		  $sheet->setCellValue('B1', 'REPORTE DOCUMENTOS VENCIDOS Y POR VENCER');
		  $sheet->setCellValue('B2', 'FECHA:');
		  $sheet->setCellValue('C2',$hoyCorregido);

		  $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		  $drawing->setName('Logo');
		  $drawing->setDescription('Logo');
		  $drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		  $drawing->setCoordinates('A1');

		  $drawing->setWorksheet($spreadsheet->getActiveSheet());

		  $spreadsheet->getActiveSheet()->getStyle('A6')->getFont()->setBold(true);
		  $spreadsheet->getActiveSheet()->getStyle('A7:F7')->getFont()->setBold(true);
		  $sheet->getColumnDimension('A')->setAutoSize(true);
		  $sheet->getColumnDimension('B')->setAutoSize(true);
			$sheet->getColumnDimension('C')->setAutoSize(true);
			$sheet->getColumnDimension('D')->setAutoSize(true);
			$sheet->getColumnDimension('E')->setAutoSize(true);
			$sheet->getColumnDimension('F')->setAutoSize(true);

		  $sheet->setCellValue('A6', 'Documentos que vencerán en los próximos 30 días');
			$sheet->setCellValue('A7', 'Empresa');
		  $sheet->setCellValue('B7', 'Tipo Documento');
		  $sheet->setCellValue('C7', 'Fecha Vencimiento');
		  $sheet->setCellValue('D7', 'Entidad');
		  $sheet->setCellValue('E7', 'Nombre Entidad');
		  $sheet->setCellValue('F7', 'RUT Entidad');

		  $j=8;

			foreach ($docsXvencer as $doc) {
			 if($doc->Entidad==3){
				 $persona = \DB::table('tbl_personas')
					 ->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
					 ->where('tbl_personas.IdPersona',$doc->IdEntidad)->first();
				 if($persona){
					 $sheet->setCellValue('A'.$j, $doc->RazonSocial);
					 $sheet->setCellValue('B'.$j, $doc->Descripcion);
					 $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					 $sheet->setCellValue('D'.$j, $doc->Enti);
					 $sheet->setCellValue('E'.$j, $persona->Nombres." ".$persona->Apellidos);
					 $sheet->setCellValue('F'.$j, $persona->RUT);
				 }

			 }
			 if($doc->Entidad==2){
				 $sheet->setCellValue('A'.$j, $doc->RazonSocial);
				 $sheet->setCellValue('B'.$j, $doc->Descripcion);
				 $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
				 $sheet->setCellValue('D'.$j, $doc->Enti);
				 $contrato = \DB::table('tbl_contrato')->where('contrato_id',$doc->IdEntidad)->first();
				 if($contrato){
					$sheet->setCellValue('E'.$j, $contrato->cont_nombre);
					$sheet->setCellValue('F'.$j, $contrato->cont_numero);
				 }
			 }
			 if($doc->Entidad==1){
				 $sheet->setCellValue('A'.$j, $doc->RazonSocial);
				 $sheet->setCellValue('B'.$j, $doc->Descripcion);
				 $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
				 $sheet->setCellValue('D'.$j, $doc->Enti);
				 $contratista = \DB::table('tbl_contratistas')->where('IdContratista',$doc->IdEntidad)->first();
				 if($contratista){
					 $sheet->setCellValue('E'.$j, $contratista->RazonSocial);
					 $sheet->setCellValue('F'.$j, $contratista->RUT);
				 }
			 }
			 if($doc->Entidad==10){
				 $sheet->setCellValue('A'.$j, $doc->RazonSocial);
				 $sheet->setCellValue('B'.$j, $doc->Descripcion);
				 $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
				 $sheet->setCellValue('D'.$j, $doc->Enti);
				 $activo = \DB::table('tbl_activos_data')
									 ->join('tbl_activos_data_detalle','tbl_activos_data.IdActivoData','=','tbl_activos_data_detalle.IdActivoData')
									 ->join('tbl_activos','tbl_activos.IdActivo','=','tbl_activos_data.IdActivo')
									 ->where('tbl_activos_data.IdActivoData',$doc->IdEntidad)->orderBy('tbl_activos_data_detalle.IdActivoDetalle','asc')->first();
				 if(isset($activo->Descripcion)) $sheet->setCellValue('E'.$j, $activo->Descripcion);
				 if(isset($activo->Valor)) $sheet->setCellValue('F'.$j, $activo->Valor);
			 }

			 $j++;
		 }
		  $j++;$j++;
		  $spreadsheet->getActiveSheet()->getStyle('A'.$j)->getFont()->setBold(true);
		  $sheet->setCellValue('A'.$j, 'Documentos que se encuentran vencidos');
		  $j++;
		  $spreadsheet->getActiveSheet()->getStyle('A'.$j.':F'.$j)->getFont()->setBold(true);
			$sheet->setCellValue('A'.$j, 'Empresa');
		  $sheet->setCellValue('B'.$j, 'Tipo Documento');
		  $sheet->setCellValue('C'.$j, 'Fecha Vencimiento');
		  $sheet->setCellValue('D'.$j, 'Entidad');
		  $sheet->setCellValue('E'.$j, 'Nombre Entidad');
		  $sheet->setCellValue('F'.$j, 'RUT Entidad');
		  $j++;
			foreach ($docsVencidos as $doc) {

		    if($doc->Entidad==3){
					$persona = \DB::table('tbl_personas')
					 ->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
					 ->where('tbl_personas.IdPersona',$doc->IdEntidad)->first();
					 if($persona){
						 $sheet->setCellValue('A'.$j, $doc->RazonSocial);
			 		    $sheet->setCellValue('B'.$j, $doc->Descripcion);
			 		    $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
			 		    $sheet->setCellValue('D'.$j, $doc->Enti);
							$sheet->setCellValue('E'.$j, $persona->Nombres." ".$persona->Apellidos);
				      $sheet->setCellValue('F'.$j, $persona->RUT);
							 $j++;
					 }

		    }
		    if($doc->Entidad==2){
					$sheet->setCellValue('A'.$j, $doc->RazonSocial);
					$sheet->setCellValue('B'.$j, $doc->Descripcion);
					$sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					$sheet->setCellValue('D'.$j, $doc->Enti);
		      $contrato = \DB::table('tbl_contrato')->where('contrato_id',$doc->IdEntidad)->first();
					if($contrato){
						$sheet->setCellValue('E'.$j, $contrato->cont_nombre);
			      $sheet->setCellValue('F'.$j, $contrato->cont_numero);
					}
					$j++;
		    }
		    if($doc->Entidad==1){
					$sheet->setCellValue('A'.$j, $doc->RazonSocial);
					$sheet->setCellValue('B'.$j, $doc->Descripcion);
					$sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					$sheet->setCellValue('D'.$j, $doc->Enti);
		      $contratista = \DB::table('tbl_contratistas')->where('IdContratista',$doc->IdEntidad)->first();
					if($contratista){
						$sheet->setCellValue('E'.$j, $contratista->RazonSocial);
			      $sheet->setCellValue('F'.$j, $contratista->RUT);
					}
					$j++;
		    }
				if($doc->Entidad==10){
					$sheet->setCellValue('A'.$j, $doc->RazonSocial);
					$sheet->setCellValue('B'.$j, $doc->Descripcion);
					$sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					$sheet->setCellValue('D'.$j, $doc->Enti);
					$activo = \DB::table('tbl_activos_data')
										->join('tbl_activos_data_detalle','tbl_activos_data.IdActivoData','=','tbl_activos_data_detalle.IdActivoData')
										->join('tbl_activos','tbl_activos.IdActivo','=','tbl_activos_data.IdActivo')
										->where('tbl_activos_data.IdActivoData',$doc->IdEntidad)->orderBy('tbl_activos_data_detalle.IdActivoDetalle','asc')->first();
					if(isset($activo->Descripcion)) $sheet->setCellValue('E'.$j, $activo->Descripcion);
					if(isset($activo->Valor)) $sheet->setCellValue('F'.$j, $activo->Valor);
					$j++;
				}
		  }

			$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
			$writer->save(storage_path("borrar/$ct->IdContratista"."_Reporte_documentos_$hoy.xlsx"));

			$excel = base_path("storage/borrar/$ct->IdContratista"."_Reporte_documentos_$hoy.xlsx");
			$destinatario = $ct->email;

			//\Log::info("enviamos correo reporte docs vencidos");
			\Mail::send('emails.informedocsvencidos',['excel'=>$excel], function ($m) use ($excel,$destinatario){
				$m->from(CNF_EMAIL);
				$m->to($destinatario);
				$m->subject("Reporte de Documentos Vencidos y por Vencer");
				$m->attach($excel);
			});

		}

	}

	public function sendDocsVencidosConsolidado(){
	  $hoy=date('Y-m-d');
	  $hoyCorregido = date('d-m-Y');

			$docsVencidos = \DB::table('tbl_documentos')
		    ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
		    ->join('tbl_entidades','tbl_documentos.Entidad','=','tbl_entidades.IdEntidad')
				->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_documentos.IdContratista')
		    ->select('tbl_tipos_documentos.Descripcion','tbl_documentos.FechaVencimiento','tbl_entidades.Entidad as Enti','tbl_tipos_documentos.Entidad', 'tbl_documentos.IdEntidad','tbl_contratistas.RazonSocial')
		    ->where('Vencimiento',1)->where('IdEstatusDocumento',2)->where('tbl_contratistas.IdContratista','>',1)->orderBy('tbl_documentos.FechaVencimiento','desc')->get();

		  $docsXvencer  = \DB::table('tbl_documentos')
		    ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
		    ->join('tbl_entidades','tbl_documentos.Entidad','=','tbl_entidades.IdEntidad')
				->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_documentos.IdContratista')
		    ->select('tbl_tipos_documentos.Descripcion','tbl_documentos.FechaVencimiento','tbl_entidades.Entidad as Enti','tbl_tipos_documentos.Entidad', 'tbl_documentos.IdEntidad','tbl_contratistas.RazonSocial')
		    ->where('Vencimiento',1)->whereBetween('FechaVencimiento',[ $hoy ,date("Y-m-d",strtotime("$hoy +30 days")) ])
				->where('IdEstatusDocumento','=',null)->where('tbl_contratistas.IdContratista','>',1)->orderBy('tbl_documentos.FechaVencimiento','asc')->get();

		  $spreadsheet = new Spreadsheet();
		  $sheet = $spreadsheet->getActiveSheet();
		  $spreadsheet->getActiveSheet()->setTitle('REPORTE');
		  $spreadsheet->setActiveSheetIndex(0);

		  $spreadsheet->getActiveSheet()->getStyle('C1')->getFont()->setSize(14)->setBold(true);
		  $sheet->setCellValue('B1', 'REPORTE DOCUMENTOS VENCIDOS Y POR VENCER');
		  $sheet->setCellValue('B2', 'FECHA:');
		  $sheet->setCellValue('C2',$hoyCorregido);

		  $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		  $drawing->setName('Logo');
		  $drawing->setDescription('Logo');
		  $drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		  $drawing->setCoordinates('A1');

		  $drawing->setWorksheet($spreadsheet->getActiveSheet());

		  $spreadsheet->getActiveSheet()->getStyle('A6')->getFont()->setBold(true);
		  $spreadsheet->getActiveSheet()->getStyle('A7:F7')->getFont()->setBold(true);
		  $sheet->getColumnDimension('A')->setAutoSize(true);
		  $sheet->getColumnDimension('B')->setAutoSize(true);
			$sheet->getColumnDimension('C')->setAutoSize(true);
			$sheet->getColumnDimension('D')->setAutoSize(true);
			$sheet->getColumnDimension('E')->setAutoSize(true);
			$sheet->getColumnDimension('F')->setAutoSize(true);

		  $sheet->setCellValue('A6', 'Documentos que vencerán en los próximos 30 días');
			$sheet->setCellValue('A7', 'Empresa');
		  $sheet->setCellValue('B7', 'Tipo Documento');
		  $sheet->setCellValue('C7', 'Fecha Vencimiento');
		  $sheet->setCellValue('D7', 'Entidad');
		  $sheet->setCellValue('E7', 'Nombre Entidad');
		  $sheet->setCellValue('F7', 'RUT Entidad');

		  $j=8;

		  foreach ($docsXvencer as $doc) {
		    if($doc->Entidad==3){
		      $persona = \DB::table('tbl_personas')
						->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
						->where('tbl_personas.IdPersona',$doc->IdEntidad)->first();
					if($persona){
						$sheet->setCellValue('A'.$j, strtoupper($doc->RazonSocial));
				    $sheet->setCellValue('B'.$j, $doc->Descripcion);
				    $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
				    $sheet->setCellValue('D'.$j, $doc->Enti);
						$sheet->setCellValue('E'.$j, $persona->Nombres." ".$persona->Apellidos);
			      $sheet->setCellValue('F'.$j, $persona->RUT);
						$j++;
					}

		    }
		    if($doc->Entidad==2){
					$sheet->setCellValue('A'.$j, strtoupper($doc->RazonSocial));
					$sheet->setCellValue('B'.$j, $doc->Descripcion);
					$sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					$sheet->setCellValue('D'.$j, $doc->Enti);
		      $contrato = \DB::table('tbl_contrato')->where('contrato_id',$doc->IdEntidad)->first();
					if($contrato){
						$sheet->setCellValue('E'.$j, $contrato->cont_nombre);
			      $sheet->setCellValue('F'.$j, $contrato->cont_numero);
					}
					$j++;
		    }
		    if($doc->Entidad==1){
					$sheet->setCellValue('A'.$j, strtoupper($doc->RazonSocial));
					$sheet->setCellValue('B'.$j, $doc->Descripcion);
					$sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					$sheet->setCellValue('D'.$j, $doc->Enti);
		      $contratista = \DB::table('tbl_contratistas')->where('IdContratista',$doc->IdEntidad)->first();
					if($contratista){
						$sheet->setCellValue('E'.$j, $contratista->RazonSocial);
			      $sheet->setCellValue('F'.$j, $contratista->RUT);
					}
					$j++;
		    }
				if($doc->Entidad==10){
					$sheet->setCellValue('A'.$j, strtoupper($doc->RazonSocial));
					 $sheet->setCellValue('B'.$j, $doc->Descripcion);
					 $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					 $sheet->setCellValue('D'.$j, $doc->Enti);
					 $activo = \DB::table('tbl_activos_data')
 										->join('tbl_activos_data_detalle','tbl_activos_data.IdActivoData','=','tbl_activos_data_detalle.IdActivoData')
 										->join('tbl_activos','tbl_activos.IdActivo','=','tbl_activos_data.IdActivo')
 										->where('tbl_activos_data.IdActivoData',$doc->IdEntidad)->orderBy('tbl_activos_data_detalle.IdActivoDetalle','asc')->first();
 					if(isset($activo->Descripcion)) $sheet->setCellValue('E'.$j, $activo->Descripcion);
 					if(isset($activo->Valor)) $sheet->setCellValue('F'.$j, $activo->Valor);
					 $j++;
				}

		  }
		  $j++;$j++;
		  $spreadsheet->getActiveSheet()->getStyle('A'.$j)->getFont()->setBold(true);
		  $sheet->setCellValue('A'.$j, 'Documentos que se encuentran vencidos');
		  $j++;
		  $spreadsheet->getActiveSheet()->getStyle('A'.$j.':F'.$j)->getFont()->setBold(true);
			$sheet->setCellValue('A'.$j, 'Empresa');
		  $sheet->setCellValue('B'.$j, 'Tipo Documento');
		  $sheet->setCellValue('C'.$j, 'Fecha Vencimiento');
		  $sheet->setCellValue('D'.$j, 'Entidad');
		  $sheet->setCellValue('E'.$j, 'Nombre Entidad');
		  $sheet->setCellValue('F'.$j, 'RUT Entidad');
		  $j++;
		  foreach ($docsVencidos as $doc) {

		    if($doc->Entidad==3){
					$persona = \DB::table('tbl_personas')
					 ->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
					 ->where('tbl_personas.IdPersona',$doc->IdEntidad)->first();
					 if($persona){
						 $sheet->setCellValue('A'.$j, strtoupper($doc->RazonSocial));
			 		    $sheet->setCellValue('B'.$j, $doc->Descripcion);
			 		    $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
			 		    $sheet->setCellValue('D'.$j, $doc->Enti);
							$sheet->setCellValue('E'.$j, $persona->Nombres." ".$persona->Apellidos);
				      $sheet->setCellValue('F'.$j, $persona->RUT);
							 $j++;
					 }

		    }
		    if($doc->Entidad==2){
					$sheet->setCellValue('A'.$j, strtoupper($doc->RazonSocial));
					 $sheet->setCellValue('B'.$j, $doc->Descripcion);
					 $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					 $sheet->setCellValue('D'.$j, $doc->Enti);
		      $contrato = \DB::table('tbl_contrato')->where('contrato_id',$doc->IdEntidad)->first();
					if($contrato){
		      	$sheet->setCellValue('E'.$j, $contrato->cont_nombre);
		      	$sheet->setCellValue('F'.$j, $contrato->cont_numero);
					}else{
					//	\Log::info("IdDocumento: ".$doc->IdEntidad);
					}
					 $j++;
		    }
		    if($doc->Entidad==1){
					$sheet->setCellValue('A'.$j, strtoupper($doc->RazonSocial));
					 $sheet->setCellValue('B'.$j, $doc->Descripcion);
					 $sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					 $sheet->setCellValue('D'.$j, $doc->Enti);
		      $contratista = \DB::table('tbl_contratistas')->where('IdContratista',$doc->IdEntidad)->first();
					if($contratista){
						$sheet->setCellValue('E'.$j, $contratista->RazonSocial);
			      $sheet->setCellValue('F'.$j, $contratista->RUT);
					}
					 $j++;
		    }
				if($doc->Entidad==10){
					$sheet->setCellValue('A'.$j, $doc->RazonSocial);
					$sheet->setCellValue('B'.$j, $doc->Descripcion);
					$sheet->setCellValue('C'.$j, $doc->FechaVencimiento);
					$sheet->setCellValue('D'.$j, $doc->Enti);
					$activo = \DB::table('tbl_activos_data')
										->join('tbl_activos_data_detalle','tbl_activos_data.IdActivoData','=','tbl_activos_data_detalle.IdActivoData')
										->join('tbl_activos','tbl_activos.IdActivo','=','tbl_activos_data.IdActivo')
										->where('tbl_activos_data.IdActivoData',$doc->IdEntidad)->orderBy('tbl_activos_data_detalle.IdActivoDetalle','asc')->first();
					if(isset($activo->Descripcion)) $sheet->setCellValue('E'.$j, $activo->Descripcion);
					if(isset($activo->Valor)) $sheet->setCellValue('F'.$j, $activo->Valor);
					$j++;
				}

		  }

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename='."Reporte_documentos_vencidos_$hoy.xlsx");
			header('Cache-Control: max-age=0');

			$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
			$writer->save('php://output');
			exit;



	}

	public function checkacceso($rut, $fecha = null){
		if(isset($fecha)){
			$entrada = \DB::table('tbl_accesos_dispositivo')->where('acceso','entrada')->where('fecha_marca','like',$fecha." %")->where('rut',$rut)->first();
			if($entrada){
				return '1';
			}else{
				return '0';
			}
		}else{
			$entrada = \DB::table('tbl_accesos_dispositivo')->where('acceso','entrada')->where('rut',$rut)->count();
			return $entrada;
		}

	}

	public function getTarjetaAcceso(Request $request){
		$lintIdAcceso = $request->idAcceso;
		$datos = Accesos::join('tbl_acceso_areas','tbl_acceso_areas.IdAcceso','=','tbl_accesos.IdAcceso')->where('tbl_accesos.IdAcceso',$lintIdAcceso)->first();
		$user = \DB::table('tb_users')->where('id',$datos->entry_by)->first();
		$qr= QrCode::size(150)->generate($datos->data_rut);
		$IdTipoAcceso=$datos->IdTipoAcceso;
		$lintIdAreaTrabajo = $datos->IdAreaTrabajo;
		$areaTrabajo = \DB::table('tbl_area_de_trabajo')->where('IdAreaTrabajo',$lintIdAreaTrabajo)->value('Descripcion');
		switch ($IdTipoAcceso) {
			case '2':
					$tipoacceso='Emergencia';
				break;
			case '3':
					$tipoacceso='Provisional';
				break;
			case '4':
					$tipoacceso='Provisional Conductor';
				break;
			default:
					$tipoacceso='Provisional';
				break;
		}
		if($IdTipoAcceso==4){
			$vehiculos = \DB::table('tbl_accesos_patentes')->where('IdAcceso',$lintIdAcceso)->get();
		}else{
			$vehiculos = '';
		}

		$pdf = PDF::loadView('accesos.tarjetaacceso',array('datos'=>$datos,'qr'=>$qr,'user'=>$user,'tipoacceso'=>$tipoacceso,'vehiculos'=>$vehiculos,'IdTipoAcceso'=>$IdTipoAcceso,'areatrabajo'=>$areaTrabajo))
			->setOption('page-width', '125')
			->setOption('page-height', '155')
			->setOption('margin-top',0)
      ->setOption('margin-bottom',0)
      ->setOption('margin-left',0)
      ->setOption('margin-right',0);
		return $pdf->download('TarjetaAcceso.pdf');

		//return view('accesos.tarjetaacceso')->with('datos',$datos)->with('qr',$qr);
	}

	public function getTarjetaAccesoCCU(Request $request,$enviaEmail=null){
		$lintIdAcceso = $request->idAcceso;
		$datos = Accesos::join('tbl_acceso_areas','tbl_acceso_areas.IdAcceso','=','tbl_accesos.IdAcceso')->where('tbl_accesos.IdAcceso',$lintIdAcceso)->first();
		$user = \DB::table('tb_users')->where('id',$datos->entry_by)->first();
		$qr= QrCode::size(150)->generate($datos->data_rut);
		$IdTipoAcceso=$datos->IdTipoAcceso;
		$lintIdAreaTrabajo = $datos->IdAreaTrabajo;
		$areaTrabajo = \DB::table('tbl_area_de_trabajo')->where('IdAreaTrabajo',$lintIdAreaTrabajo)->value('Descripcion');
		switch ($IdTipoAcceso) {
			case '2':
					$tipoacceso='Emergencia';
				break;
			case '3':
					$tipoacceso='Provisional';
				break;
			case '4':
					$tipoacceso='Provisional Conductor';
				break;
			default:
					$tipoacceso='Provisional';
				break;
		}
		if($IdTipoAcceso==4){
			$vehiculos = \DB::table('tbl_accesos_patentes')->where('IdAcceso',$lintIdAcceso)->get();
		}else{
			$vehiculos = '';
		}

		$centro=[];
		$uen=[];

		$uensyct = \DB::table('tbl_acceso_areas')
			->join('tbl_uen_ct','tbl_uen_ct.uenct_id','=','tbl_acceso_areas.IdAreaTrabajo')
			->join('tbl_centro_trabajo','tbl_centro_trabajo.ct_id','=','tbl_uen_ct.ct_id')
			->join('tbl_centro','tbl_centro.IdCentro','=','tbl_uen_ct.uen_id')
			->where('tbl_acceso_areas.IdAcceso',$lintIdAcceso)
			->get();
		foreach ($uensyct as $dato) {
			array_push($centro,$dato->descripcion);
			array_push($uen,$dato->Descripcion);
		}
		$centro = implode(",", $centro);
		$uen = implode(",", $uen);

		if($enviaEmail){

			$pdf = PDF::loadView('accesos.tarjetaaccesoccu',
			array('datos'=>$datos,'qr'=>$qr,'user'=>$user,'tipoacceso'=>$tipoacceso,'vehiculos'=>$vehiculos,'IdTipoAcceso'=>$IdTipoAcceso,'areatrabajo'=>$areaTrabajo,'uen'=>$uen,'centros'=>$centro));

      \Mail::send('emails.tarjetaaccesoccu', [], function($message)use($datos, $pdf) {
          $message->from(CNF_EMAIL)
									->to($datos["email"])
                  ->subject("Pase de visita")
                  ->attachData($pdf->output(), "TarjetaAcceso.pdf");
      });
		}else{
			$pdf = PDF::loadView('accesos.tarjetaaccesoccu',
				array(
					'datos'=>$datos,
					'qr'=>$qr,
					'user'=>$user,
					'tipoacceso'=>$tipoacceso,
					'vehiculos'=>$vehiculos,
					'IdTipoAcceso'=>$IdTipoAcceso,
					'areatrabajo'=>$areaTrabajo,
					'uen'=>$uen,
					'centros'=>$centro
				))
				->setOption('margin-top',0)
	      ->setOption('margin-bottom',0)
	      ->setOption('margin-left',0)
	      ->setOption('margin-right',0);
			return $pdf->download('TarjetaAcceso.pdf');
		}

	}

	public function informeAccesosSinAccesos(){

		$hoy = date('d-m-Y');

		$personas = \DB::select(\DB::raw("(SELECT p.rut, p.Nombres, p.Apellidos, cp.contrato_id, cp.IdContratista, ct.RazonSocial, c.cont_numero, max(ad.fecha_marca) AS ultimoAcceso FROM tbl_accesos_dispositivo ad
								JOIN tbl_personas p ON p.RUT=ad.rut
								JOIN tbl_contratos_personas cp ON cp.IdPersona=p.IdPersona
								JOIN tbl_contratistas ct ON ct.IdContratista=cp.IdContratista
								JOIN tbl_contrato c ON c.contrato_id=cp.contrato_id
								WHERE ad.fecha_marca not BETWEEN  DATE_SUB(NOW(), INTERVAL 21 DAY) AND NOW()
								AND ad.acceso='entrada' AND NOT EXISTS (SELECT 1 FROM tbl_accesos_dispositivo ad2 WHERE ad2.rut=ad.rut AND ad2.acceso='entrada' and ad2.fecha_marca BETWEEN  DATE_SUB(NOW(), INTERVAL 21 DAY) AND NOW())
								GROUP BY p.IdPersona)
								UNION
								(SELECT p.rut, p.Nombres, p.Apellidos, cp.contrato_id, cp.IdContratista, ct.RazonSocial, c.cont_numero, 's/i' AS ultimoAcceso FROM tbl_personas p
								JOIN tbl_contratos_personas cp ON cp.IdPersona=p.IdPersona
								JOIN tbl_contrato c ON c.contrato_id=cp.contrato_id
								JOIN tbl_contratistas ct ON ct.IdContratista=cp.IdContratista
								WHERE NOT EXISTS (SELECT 1 FROM tbl_accesos_dispositivo ad WHERE ad.rut=p.rut) AND c.cont_estado!=2)"));

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('REPORTE 21 DIAS');
		$spreadsheet->setActiveSheetIndex(0);

		$spreadsheet->getActiveSheet()->getStyle('C1')->getFont()->setSize(13)->setBold(true);
		//$sheet->setCellValue('C1', 'REPORTE DIARIO RUT SIN ACCESO ÚLTIMOS 21 DÍAS EECC OHL PARA MANTOS BLANCOS');
		$sheet->setCellValue('C1', 'REPORTE DIARIO RUT SIN ACCESO ÚLTIMOS 21 DÍAS EECC');
		$sheet->setCellValue('C2','Fecha:');
		$sheet->setCellValue('D2',$hoy);

		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('A1');

		$drawing->setWorksheet($spreadsheet->getActiveSheet());

		$spreadsheet->getActiveSheet()->setAutoFilter('A7:F7');
		$spreadsheet->getActiveSheet()->getStyle('A7:F7')->getFont()->setBold(true);
		$sheet->getColumnDimension('A')->setWidth(30);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setWidth(20);
		$sheet->getColumnDimension('D')->setWidth(30);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->setCellValue('A7', 'EMPRESA');
		$sheet->setCellValue('B7', 'CONTRATO');
		$sheet->setCellValue('C7', 'RUT');
		$sheet->setCellValue('D7', 'NOMBRE');
		$sheet->setCellValue('E7', 'APELLIDOS');
		$sheet->setCellValue('F7', 'ÚLTIMO ACCESO');

		$j=8;

		foreach ($personas as $persona) {
			$sheet->setCellValue('A'.$j, strtoupper(strtolower($persona->RazonSocial)));
			$sheet->setCellValue('B'.$j, $persona->cont_numero);
			$sheet->setCellValue('C'.$j, $persona->rut);
			$sheet->setCellValue('D'.$j, strtoupper($persona->Nombres));
			$sheet->setCellValue('E'.$j, strtoupper($persona->Apellidos));
			$sheet->setCellValue('F'.$j, strtoupper($persona->ultimoAcceso));
			$j++;
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Reporte_21_'.$hoy.'.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;

	}

	public function getShowlist(Request $request){

		$fechaHoy=date('Y-m-d');
		$consultaAcceso = \DB::table('tbl_accesos')
		->selectRaw('tbl_accesos.IdAcceso,tbl_accesos.FechaFinal')
		->where('tbl_accesos.IdEstatusUsuario','=', 1)
		->get();
		 foreach ($consultaAcceso as $key => $pase){
			\DB::table('tbl_accesos')
			->where('tbl_accesos.FechaFinal','=',$pase->FechaFinal)
			->where('tbl_accesos.FechaFinal','<',$fechaHoy)
			->update(["IdEstatusUsuario"=>2]);
		}
		 // Get Query
       $sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
		// End Filter sort and order for query
		// Filter Search for query
		$filter = '';
		if(!is_null($request->input('search')))
		{
			$search = $this->buildSearch('maps');
			$filter = $search['param'];
			$this->data['search_map'] = $search['maps'];
		}
    $lintIdUser = \Session::get('uid');
    $lintLevelUser = \MySourcing::LevelUser($lintIdUser);

        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);

        if ($lintLevelUser==1 || $lintLevelUser==7) {
            $filter .= " ";
        }
        else{
            $filter .= " AND tbl_accesos.contrato_id IN (".$lobjFiltro['contratos'].') OR tbl_accesos.entry_by_access = '.$lintIdUser;

        }

		$params = array(
			'page'		=> '',
			'limit'		=> '',
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query
		$results = $this->model->getRows( $params );

		$larrResult = array();
		$larrResultTemp = array();
		$i = 0;

		foreach ($results['rows'] as $row) {

			$id = $row->IdAcceso;

			$larrResultTemp = array('id'=> ++$i,
								    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$id.'" /> '
								    );
			foreach ($this->info['config']['grid'] as $field) {
				if($field['view'] =='1') {
					$limited = isset($field['limited']) ? $field['limited'] :'';
					if (\SiteHelpers::filterColumn($limited )){
						$value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
						if ($field['field']=="IdEstatusUsuario"){
						    if ($row->{"IdEstatus"} != $row->{"IdEstatusUsuario"}) {
                                if ($row->{"FechaFinal"} >= date('Y-m-d')) {
                                    $value = $value . ' <span class="btnViewMotivo" title="El estado del trabajador es distinto al estatus del acceso" data-id-acceso="' . $row->IdPersona . '"><i class="fa fa-exclamation-circle"></i></span>';
                                }
                            }
						}
                        if ($field['field']=="FechaFinal" && $row->{"FechaFinal"} < date('Y-m-d') ){
                            $value.= " <span class=\"btnViewMotivo\" title=\"Acceso vencido\" data-id-acceso=\"'.$row->IdPersona.'\"><i class=\"fa fa-exclamation-circle\"></i></span>";
                        }

						$larrResultTemp[$field['field']] = $value;
					}
				}
			}

			$module = 'accesos';
			$access = $this->access;
			$setting = $this->info['setting'];

			$html ='<div class=" action dropup" >';
			if($access['is_edit'] ==1) {
				$onclick = " onclick=\"ajaxViewDetail('#".$module."',this.href); return false; \"" ;
				if($setting['form-method'] =='modal')
						$onclick = " onclick=\"AccesosSximoModal(this.href,'Edit Form'); return false; \"" ;

				$html .= ' <a href="'.\URL::to($module.'/update/'.$id).'" '.$onclick.'  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-edit"></i></a>';
			}
			if($access['is_edit'] ==1) {
				$onclick = " onclick=\"ajaxViewDetail('#".$module."',this.href); return false; \"" ;
				if($setting['form-method'] =='dropup')
						$onclick = " onclick=\"AccesosSximoModal(this.href,'Edit Form'); return false; \"" ;
			}
			$html .= '<a data-id-acceso="'.$id.'" data-url="'.\URL::to($module.'/infoadicional/'.$id.'/'.$row->IdPersona).'" class="btn btn-xs btn-white tips details-control rotate" title="Ver detalle"><i class="fa fa-arrow-right"></i></a>';
			$sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
			if($sitio->Valor=='Ohl Industrial'){
				$html .= '<a href="accesos/tarjetaacceso?idAcceso='.$id.'" class="btn btn-xs btn-white tips details-control"><i class="icon-vcard"></i></a>';
			}
			if($sitio->Valor=='CCU'){
				$html .= '<a href="accesos/tarjetaaccesoccu?idAcceso='.$id.'" class="btn btn-xs btn-white tips details-control"><i class="icon-vcard"></i></a>';
			}
			$html .= '</div>';

			$larrResultTemp['action'] = $html;
			$larrResult[] = $larrResultTemp;
		}

		echo json_encode(array("data"=>$larrResult));

	}

	function getUpdate(Request $request, $id = null)
	{
		if($id =='')
		{
			if($this->access['is_add'] ==0 )
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		}

		if($id !='')
		{
			if($this->access['is_edit'] ==0 )
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		}

		$this->data['cont_numero'] = '';
		$this->data['email']='';
		$row = $this->model->find($id);
		if($row)
		{
			$this->data['row'] 		=  $row;
			$lintIdTipoAcceso       =  $row->IdTipoAcceso;

			if (isset($this->data['row']->contrato_id) && $this->data['row']->contrato_id) {
				$this->data['cont_numero'] = \DB::table('tbl_contrato')
				->where('contrato_id','=',$this->data['row']->contrato_id)
				->first();
				if ($this->data['cont_numero']){
					$this->data['cont_numero'] = $this->data['cont_numero']->cont_numero;
				}
			}
			$this->data['email']=$row->email;
		} else {
			$this->data['row'] = $this->model->getColumnTable('tbl_accesos');
			//var_dump($this->data['selectCentros']);
		}

		$sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();

		if($sitio->Valor=='CCU'){

			//centros = uen
			$this->data['selectCentros'] = \DB::table('tbl_centro')
				->join('tbl_uen_ct','tbl_centro.IdCentro','=','tbl_uen_ct.uen_id')
				->join('tbl_centro_trabajo','tbl_centro_trabajo.ct_id','=','tbl_uen_ct.ct_id')
				->select(
					\DB::raw("tbl_uen_ct.uenct_id as value"),
					\DB::raw("tbl_centro.Descripcion as Centro"),
					\DB::raw("tbl_centro_trabajo.descripcion as Area"),
					\DB::raw("0 as AreaAsignada")
					)
				->orderby("tbl_centro.Descripcion","ASC")
				->get();
				
		}else{
		//Buscamos las areas de los accesos
		$this->data['selectCentros'] = \DB::table('tbl_centro')
		->select( \DB::raw("tbl_area_de_trabajo.IdAreaTrabajo as value"),
						\DB::raw("tbl_centro.Descripcion as Centro"),
						\DB::raw("tbl_area_de_trabajo.Descripcion as Area"),
					\DB::raw("tbl_acceso_areas.IdAcceso as AreaAsignada")
				)
		->join("tbl_area_de_trabajo", "tbl_centro.IdCentro", "=", "tbl_area_de_trabajo.IdCentro")
		->where("tbl_area_de_trabajo.IdTipoAcceso", "!=", 2)
		->orderby("tbl_centro.Descripcion","ASC")
		->orderby("tbl_area_de_trabajo.Descripcion","ASC")
		->Leftjoin('tbl_acceso_areas', function ($join) use ($id) {
					$join->on('tbl_acceso_areas.IdAreaTrabajo', '=', 'tbl_area_de_trabajo.IdAreaTrabajo');
						if ($id){
							$join = $join->on('tbl_acceso_areas.IdAcceso', '=', \DB::raw($id));
						}else{
							$join = $join->on('tbl_acceso_areas.IdAcceso', 'IS', \DB::raw('NULL'));
						}
				})
		->get();
		}

		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;


		if (isset($lintIdTipoAcceso) && $lintIdTipoAcceso==1) {
			// Recuperamos los datos de la persona
			$lobjPersona = \DB::table('tbl_personas')
			->where("tbl_personas.IdPersona","=",$row->IdPersona)
			->first();
			if ($lobjPersona){
				$row->data_rut = $lobjPersona->RUT;
				$row->data_nombres = $lobjPersona->Nombres;
				$row->data_apellidos = $lobjPersona->Apellidos;
			}
			$lobjUsuario = \DB::table('tb_users')
			->where("tb_users.Id","=",$row->updated_by)
			->first();
			if ($lobjUsuario){
				$row->{'updated_by_name'} = $lobjUsuario->first_name.' '.$lobjUsuario->last_name;
			}else{
				$row->{'updated_by_name'} = '';
			}
			return view('accesos.trabajador',$this->data);
		}elseif(isset($lintIdTipoAcceso) && $lintIdTipoAcceso==4){
			$lintIdAcceso = $row['IdAcceso'];
			$vehiculos = \DB::table('tbl_accesos_patentes')->where('IdAcceso',$lintIdAcceso)->get();
			$this->data['vehiculos']=$vehiculos;
			return view('accesos.provisorio',$this->data);
		}else{
			return view('accesos.provisorio',$this->data);
		}
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$row = $this->model->getRow($id);
		if($row)
		{
			$this->data['row'] =  $row;

			$this->data['id'] = $id;
			$this->data['access']		= $this->access;
			$this->data['setting'] 		= $this->info['setting'];
			$this->data['fields'] 		= \AjaxHelpers::fieldLang($this->info['config']['grid']);
			$this->data['subgrid']		= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
			return view('accesos.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_accesos ") as $column)
        {
			if( $column->Field != 'IdAcceso')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_accesos (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_accesos WHERE IdAcceso IN (".$toCopy.")";
			\DB::select($sql);
			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success')
			));

		} else {
			return response()->json(array(
				'status'=>'success',
				'message'=> 'Please select row to copy'
			));
		}


	}

	function postSave( Request $request, $id =0)
	
    {


    	$lintIdUser = \Session::get('uid');
        $rules = $this->validateForm();
        $validator = Validator::make($request->all(), $rules);

        //remplazamos las fechas por el formato correcta
        if (isset($_POST['FechaInicio'])){
            $_POST['FechaInicio'] = self::FormatoFecha($_POST['FechaInicio']);
        }
        if (isset($_POST['FechaFinal'])) {
            $_POST['FechaFinal'] = self::FormatoFecha($_POST['FechaFinal']);
        }

        if ($request->input('IdAcceso')){ //Es una edición

        	$lobjAcceso = \DB::table('tbl_accesos')
        	->where('tbl_accesos.IdAcceso','=',$request->input('IdAcceso'))
        	->first();

        	if ($lobjAcceso) {

	        	$data['IdTipoAcceso'] = $lobjAcceso->IdTipoAcceso;
	        	$data['IdEstatusUsuario'] = $request->IdEstatusUsuario;
	        	$data['data_nombres'] = $request->data_nombres;
	        	$data['data_apellidos'] = $request->data_apellidos;
	        	$data['Observacion'] = $request->Observacion;
	        	$data['updated_by'] = $lintIdUser;
	        	$data['updatedOn'] = date('Y-m-d H:i:s');
						$data['email'] = $request->email;

	        	if ($data['IdEstatusUsuario'] != $lobjAcceso->IdEstatusUsuario || $data['data_nombres'] != $lobjAcceso->data_nombres || $data['data_apellidos'] != $lobjAcceso->data_apellidos){
	        		if ($data['IdTipoAcceso']!=1){
	        			$data['IdEstatus'] = $data['IdEstatusUsuario'];
	        		}
	        		$id = $this->model->insertRow($data, $request->input('IdAcceso'));
				}

				//Editamos las areas
				$larrAreasTrabajo = $request->IdAreaTrabajoAdd;
                $larrAreasTrabajo = explode(",",$larrAreasTrabajo);
                $larrDataAreas = array();

                foreach ($larrAreasTrabajo as $larrAreaTrabajo) {
                	if ($larrAreaTrabajo) {
                		$larrDataAreas[] = array("IdAcceso" => $request->input('IdAcceso'),
                								 "IdAreaTrabajo"=>$larrAreaTrabajo, "entry_by" => $lintIdUser);
                	}
                }

                if ($larrDataAreas) {
                	\DB::table("tbl_acceso_areas")->insert($larrDataAreas);
                }

                //Eliminamos las areas que estén marcadas para eliminar
                $larrAreasTrabajoDelete = $request->IdAreaTrabajoDelete;
                $larrAreasTrabajoDelete = explode(",",$larrAreasTrabajoDelete);

                foreach ($larrAreasTrabajoDelete as $larrAreaTrabajo) {
                	if ($larrAreaTrabajo){
	                	\DB::table("tbl_acceso_areas")
	                	->where("IdAcceso", '=', $request->input('IdAcceso'))
	                	->where("IdAreaTrabajo", "=", $larrAreaTrabajo)
	                	->delete();
                	}
                }

	        	return response()->json(array(
		                    'status' => 'success',
		                    'message' => \Lang::get('core.note_success')
		                ));
        	}else{

        		return response()->json(array(
		                    'status' => 'error',
		                    'message' => 'El acceso que intenta editar no existe id='.$id
		                ));

        	}

        }else{ // Es una creación

        	$fechaInicioForm = $_POST['FechaInicio'];
	        $dateToday = date('Y-m-d');

	        if ($fechaInicioForm >= $dateToday) {

	            if ($validator->passes()) {

	                $data = $this->validatePost('tbl_accesos');
	                $data['data_rut'] = trim($request->data_rut);
	                $data['data_nombres'] = trim($request->data_nombres);
	                $data['data_apellidos'] = $request->data_apellidos;
	                $data['IdEstatus'] = $request->IdEstatusUsuario;
					$data['email'] = $request->email;

	                // validamos si la persona ya tiene creado un acceso provisorio entre las fechas comprendidas
	                $lobjExistenteAcceso = \DB::table('tbl_accesos')
	                ->where('tbl_accesos.IdTipoAcceso','!=','1')
	                ->where('tbl_accesos.data_rut','=',$data['data_rut'])
	                ->where(function ($query) use ($data){
				    		$query->whereraw('"'.$data['FechaInicio'].'" > tbl_accesos.FechaInicio AND "'.$data['FechaInicio'].'" < tbl_accesos.FechaFinal')
				    		      ->orwhereraw('"'.$data['FechaFinal'].'" > tbl_accesos.FechaInicio AND "'.$data['FechaFinal'].'" < tbl_accesos.FechaFinal')
				    		      ->orwhereBetween('tbl_accesos.FechaInicio', [$data['FechaInicio'], $data['FechaFinal']])
				    		      ->orWhereBetween('tbl_accesos.FechaFinal', [$data['FechaInicio'], $data['FechaFinal']]);
	                })
	                ->get();

	           		if ($lobjExistenteAcceso){
			            return response()->json(array(
			                'message' => 'Ya existe un acceso creado para esta persona entre las fechas seleccionada',
			                'status' => 'error'
			            ));
	           		}

	                $id = $this->model->insertRow($data, $request->input('IdAcceso'));

	                $larrAreasTrabajo = $request->IdAreaTrabajoAdd;
	                $larrAreasTrabajo = explode(",",$larrAreasTrabajo);
	                $larrDataAreas = array();

	                foreach ($larrAreasTrabajo as $larrAreaTrabajo) {
	                	$larrDataAreas[] = array("IdAcceso" => $id,"IdAreaTrabajo"=>$larrAreaTrabajo, "entry_by" => $lintIdUser);
	                }

	                \DB::table("tbl_acceso_areas")->insert($larrDataAreas);

									if(isset($request->email) and $request->email!=''){
										$requestEmail = new \Illuminate\Http\Request();
										$requestEmail->replace(['idAcceso' => $id]);
										self::getTarjetaAccesoCCU($requestEmail,true);
									}

	                if($request->IdTipoAcceso==4){ //pase provisorio conductor

						$patentes = explode('|',$request->arraypatentes);

						foreach ($patentes as $k => $patente) {
							if($patente!='')\DB::table('tbl_accesos_patentes')->insert(['IdAcceso'=>$id,'patente'=>$patente]);
						}

					}

	                return response()->json(array(
	                    'status' => 'success',
	                    'message' => \Lang::get('core.note_success')
	                ));

	            } else {

	                $message = $this->validateListError($validator->getMessageBag()->toArray());
	                return response()->json(array(
	                    'message' => $message,
	                    'status' => 'error'
	                ));
	            }
	        } else {

	            $message = "error en el guardado, la fecha es inferior a hoy ".\MyFormats::FormatDate($dateToday)." con respecto a la fecha ingresada ".\MyFormats::FormatDate($fechaInicioForm);
	            return response()->json(array(
	                'message' => $message,
	                'status' => 'error'
	            ));


	        }
        }

    }

    public function postDelete( Request $request)
	{

		if($this->access['is_remove'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));
			die;
		}
		// delete multipe rows
		if(count($request->input('ids')) >=1)
		{
			\DB::table('tbl_acceso_areas')->whereIn('IdAcceso',$request->input('ids'))->delete();
			$this->model->destroy($request->input('ids'));
			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success_delete')
			));
		} else {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));

		}

	}

	public static function display( )
	{
		$mode  = isset($_GET['view']) ? 'view' : 'default' ;
		$model  = new Accesos();
		$info = $model::makeInfo('accesos');

		$data = array(
			'pageTitle'	=> 	$info['title'],
			'pageNote'	=>  $info['note']

		);

		if($mode == 'view')
		{
			$id = $_GET['view'];
			$row = $model::getRow($id);
			if($row)
			{
				$data['row'] =  $row;
				$data['fields'] 		=  \SiteHelpers::fieldLang($info['config']['grid']);
				$data['id'] = $id;
				return view('accesos.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdAcceso' ,
				'order'		=> 'asc',
				'params'	=> '',
				'global'	=> 1
			);

			$result = $model::getRows( $params );
			$data['tableGrid'] 	= $info['config']['grid'];
			$data['rowData'] 	= $result['rows'];

			$page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
			$pagination = new Paginator($result['rows'], $result['total'], $params['limit']);
			$pagination->setPath('');
			$data['i']			= ($page * $params['limit'])- $params['limit'];
			$data['pagination'] = $pagination;
			return view('accesos.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_accesos');
			 $this->model->insertRow($data , $request->input('IdAcceso'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}

	public  function postDatacontrato(Request $request)
	{
		$contrato = $request->contrato;

		$id = $request->idpersona;

		$datos = \DB::table('tbl_contrato')
		 ->join('tbl_contratos_personas', 'tbl_contrato.contrato_id', '=', 'tbl_contratos_personas.contrato_id')
            		->join('tbl_personas', 'tbl_contratos_personas.IdPersona', '=', 'tbl_personas.IdPersona')
		->select('cont_numero','RUT','Nombres','Apellidos')
		->where('tbl_contrato.contrato_id', '=', $contrato)
		->where('tbl_personas.IdPersona', '=', $id)
		->get();
		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$datos,
			'message'=>\Lang::get('core.note_sucess')
			));
	}

	public  function postDatapersona(Request $request)
	{

		$id = $request->idpersona;
        if(!is_null($id)){
            $id = $request->idAcceso;
            $datos = \DB::table('tbl_accesos')
                ->select('data_rut','data_nombres','data_apellidos')
                ->where('idAcceso', '=', $id)
                ->get();
            return response()->json(array(
                'status'=>'sucess',
                'valores'=>$datos,
                'message'=>\Lang::get('core.note_sucess')
            ));
        } else {
            $datos = \DB::table('tbl_personas')
                ->select('RUT','Nombres','Apellidos')
                ->where('IdPersona', '=', $id)
                ->get();
            return response()->json(array(
                'status'=>'sucess',
                'valores'=>$datos,
                'message'=>\Lang::get('core.note_sucess')
            ));
        }
	}
	public function postDatasarea(Request $request){
		$id = $request->id;
		$res = $request->area;
		$datos = \DB::table('tbl_area_de_trabajo')
		->select('IdAreaTrabajo','Descripcion')
		->where('IdCentro', '=', $id)
		->get();
		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$datos,
			'resultado'=>$res,
			'message'=>\Lang::get('core.note_sucess')
			));
	}


    private function FormatoFecha($pstrFecha){
        if ($pstrFecha){
            $larrFecha = explode("/", $pstrFecha);
            return $larrFecha[2].'-'.$larrFecha[1].'-'.$larrFecha[0];
        }
	}
	public function ReportesLogo($drawing){
		$cliente = \DB::table('tbl_configuraciones')->where('nombre','CNF_APPNAME')->first();
		  	switch ($cliente->Valor) {
				case 'CCU':
					$drawing->setPath(base_path('public/images/ccu.png'));
					break;
				case 'Ohl Industrial':
					$drawing->setPath(base_path('public/images/logoohl.png'));
					break;
				default:
					$drawing->setPath(base_path('public/images/check.png'));
					break;
			}
		 $drawing->setHeight(70);
		 //$drawing->setOffsetX(30);
		 return($drawing);
	}

}
