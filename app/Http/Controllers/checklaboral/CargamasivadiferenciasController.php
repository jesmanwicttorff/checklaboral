<?php namespace App\Http\Controllers\checklaboral;

use App\Library\MyCheckLaboral;
use App\Http\Controllers\controller;
use App\Models\checklaboral\Cargamasivadiferencias;
use App\Models\checklaboral\Diferenciacalculo;
use App\Models\Documentos;
use App\Library\MyDocumentsPrevired;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use Yajra\Datatables\Facades\Datatables;

class CargamasivadiferenciasController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'cargamasivadiferencias';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();

		$this->model = new Cargamasivadiferencias();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'cargamasivadiferencias',
			'return'	=> self::returnUrl()

		);
		\App::setLocale(CNF_LANG);
		if (defined('CNF_MULTILANG') && CNF_MULTILANG == '1') {

		$lang = (\Session::get('lang') != "" ? \Session::get('lang') : CNF_LANG);
		\App::setLocale($lang);
		}


	}

	public function getReprocesar(){

			$lobjDocumento = Documentos::where('IdTipoDocumento',112)
									 ->where('FechaEmision','2019-04-01')
									 ->where('Entidad',2)
									 ->where('IdEstatus','=',5)
									 ->get();

			foreach($lobjDocumento as $larrDocumentos){

					$lobjMyDocumentos = new MyDocumentsPrevired($larrDocumentos->IdDocumento);
					$lobjMyDocumentos::Crossing();

			}

	}

	public function getIndex( Request $request )
	{

		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$lobjMyCheckLaboral = new MyCheckLaboral();
		$this->data['Periodos'] = $lobjMyCheckLaboral->getPeriodos();
		$this->data['periodo'] = $lobjMyCheckLaboral::getPeriodo();

		return view('checklaboral.cargamasivadiferencias.index',$this->data);
	}

	public function postGenerareporte(Request $request, $pstrTipo = "general"){

		$ldatPeriodo = $request->input('periodo');
		$lbolForzar = $request->input('forzar')?$request->input('forzar'):false;
		$larrParametros = $request->input('parametros');

		if ($ldatPeriodo){

			//Generamos los documentos
			$lobjCheckLaboral = new MyCheckLaboral($ldatPeriodo);
			$larrResultadoReporte = $lobjCheckLaboral->GenerarReporteGeneral($lbolForzar);
			return json_encode($larrResultadoReporte);

		}else{
			return json_encode(array("status"=>"error","message"=>"Parametro es requerido"));
		}

	}

	public function getReporte(Request $request, $pstrTipo = "reportegeneral"){

		$ldatPeriodo = $request->input('periodo');
		$lobjMyCheckLaboral = new MyCheckLaboral($ldatPeriodo);
		$lobjResult = $lobjMyCheckLaboral->downloadreport($pstrTipo);
		if ($lobjResult['status']=="success"){
			return response()->download($lobjResult['result']);
		}else{
			return '<script>alert("No se encuentra el reporte");</script>';
		}

	}

	function postShowmaestropersonas(Request $request){


		$ldatPeriodo = $request->input('periodo');

		$lobjMyCheckLaboral = new MyCheckLaboral($ldatPeriodo);
		$lobjMaestroPersonas = \DB::table('tbl_personas_maestro')
											 ->select('tbl_personas.rut',
									              \DB::raw("concat(ifnull(tbl_personas.nombres,''),' ',ifnull(tbl_personas.apellidos,'')) as nombres"),
																'tbl_contrato.cont_numero',
																'tbl_personas_maestro.Estatus',
																\DB::raw('DATE_FORMAT(tbl_personas_maestro.FechaEfectiva, "%d/%m/%Y") as FechaEfectiva'),
																\DB::raw('count(distinct(tbl_diferencias_nc_personas.IdDocumento)) as NoConformidades'))
							->join('tbl_personas','tbl_personas.IdPersona','=','tbl_personas_maestro.IdPersona')
							->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_personas_maestro.contrato_id')
							->leftjoin('tbl_diferencias_nc_personas',function($table){
								$table->on('tbl_diferencias_nc_personas.IdPersona','=','tbl_personas_maestro.IdPersona')
											->on('tbl_diferencias_nc_personas.contrato_id','=','tbl_personas_maestro.contrato_id')
											->on('tbl_diferencias_nc_personas.periodo','=','tbl_personas_maestro.periodo')
											->on('tbl_diferencias_nc_personas.IdEstatusDocumento','!=',\DB::raw('5'));
							})
							->whereraw("tbl_personas_maestro.periodo = '".$lobjMyCheckLaboral::getPeriodo()."'")
							->groupBy('tbl_personas.rut',
												'tbl_personas.nombres', 
												'tbl_personas.apellidos', 
												'tbl_contrato.cont_numero',
												'tbl_personas_maestro.Estatus',
												'tbl_personas_maestro.FechaEfectiva'
											);

		$lobjMaestroPersonasTemp = \DB::table(\DB::raw("({$lobjMaestroPersonas->toSql()}) as tbl_personas_maestro"))->select(\DB::raw("*"))->get();

		echo json_encode(array("data"=>$lobjMaestroPersonasTemp));

}

	function postShownoconformidades(Request $request){

			$lobjMyCheckLaboral = new MyCheckLaboral();
			//$lobjDiferencias = $lobjMyCheckLaboral->getDiferencias();
			$lobjDiferencias = \DB::table('tbl_diferencias_nc_personas')
			                   ->select('tbl_personas.rut',
										'tbl_personas.nombres',
										'tbl_documentos.fechaemision as Periodo',
										'tbl_contrato.cont_numero',
										'tbl_tipos_documentos.Descripcion as TipoDocumento',
										'tbl_documentos_estatus.Descripcion as Estatus')
								->join('tbl_personas','tbl_personas.IdPersona','=','tbl_diferencias_nc_personas.IdPersona')
								->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_diferencias_nc_personas.contrato_id')
								->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_diferencias_nc_personas.IdTipoDocumento')
								->join('tbl_documentos','tbl_documentos.IdDocumento','=','tbl_diferencias_nc_personas.IdDocumento')
								->join('tbl_documentos_estatus','tbl_documentos_estatus.IdEstatus','=','tbl_diferencias_nc_personas.IdEstatus')
								->whereraw("tbl_diferencias_nc_personas.periodo = '".$lobjMyCheckLaboral::getPeriodo()."'")
								->whereraw('tbl_diferencias_nc_personas.IdEstatusDocumento != 5')
								->whereraw('tbl_diferencias_nc_personas.IdEstatusDocumento != 0');

			$lobjDiferenciasTem = \DB::table(\DB::raw("({$lobjDiferencias->toSql()}) as tbl_diferencias_nc_personas"))->select(\DB::raw("*"));

			$lobjDataTable = Datatables::queryBuilder($lobjDiferenciasTem)->make(true);

			return $lobjDataTable;

	}

	function postUpdatedocumental(Request $request){
		
		$lobjMyCheckLaboral = new MyCheckLaboral();
		$larrResult = $lobjMyCheckLaboral->UpdateDocument();
		echo json_encode($larrResult);
		
	}

	function postShownoconformidadesempresa(Request $request){

			$lobjMyCheckLaboral = new MyCheckLaboral();
			//$lobjDiferencias = $lobjMyCheckLaboral->getDiferencias();
			$lobjDiferencias = \DB::table('tbl_diferencias_nc_empresas')
							   ->select('tbl_contratistas.rut',
						                'tbl_contratistas.RazonSocial as nombre',
										'tbl_contrato.cont_numero',
										'tbl_documentos.fechaemision as Periodo',
										'tbl_tipos_documentos.Descripcion as TipoDocumento',
										'tbl_documentos_estatus.Descripcion as Estatus')
							   	->join('tbl_contratistas','tbl_contratistas.IdContratista', '=', 'tbl_diferencias_nc_empresas.IdContratista')
							   	->leftjoin('tbl_contrato','tbl_contrato.contrato_id', '=', 'tbl_diferencias_nc_empresas.contrato_id')
							   	->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_diferencias_nc_empresas.IdTipoDocumento')
								->join('tbl_documentos','tbl_documentos.IdDocumento','=','tbl_diferencias_nc_empresas.IdDocumento')
								->join('tbl_documentos_estatus','tbl_documentos_estatus.IdEstatus','=','tbl_diferencias_nc_empresas.IdEstatus')
							   	->whereraw("tbl_diferencias_nc_empresas.periodo = '".$lobjMyCheckLaboral::getPeriodo()."'")
							   	->whereraw('tbl_diferencias_nc_empresas.IdEstatusDocumento != 5')
							   	->whereraw('tbl_diferencias_nc_empresas.IdEstatusDocumento != 0');
			
			$lobjDiferenciasTem = \DB::table(\DB::raw("({$lobjDiferencias->toSql()}) as tbl_diferencias_nc_empresas"))->select(\DB::raw("*"));

			$lobjDataTable = Datatables::queryBuilder($lobjDiferenciasTem)->make(true);

			return $lobjDataTable;

	}

	function postShowriesgo(Request $request){

			$lobjMyCheckLaboral = new MyCheckLaboral();
			//$lobjDiferencias = $lobjMyCheckLaboral->getDiferencias();
			$lobjDiferencias = \DB::table('tbl_riesgo')
												 ->leftjoin('tbl_contrato', 'tbl_contrato.contrato_id', '=', 'tbl_riesgo.contrato_id')
												 ->leftjoin('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
			                   ->where("tbl_riesgo.periodo",$lobjMyCheckLaboral::getPeriodo())
			                   ->select('tbl_contratistas.rut',
												          'tbl_contratistas.RazonSocial',
																	'tbl_riesgo.cont_numero',
																	'tbl_riesgo.riesgo_comercial',
																	'tbl_riesgo.riesgo_impuesto',
																	'tbl_riesgo.riesgo_rotacion',
																	'tbl_riesgo.riesgo_ausentismo',
																	\DB::raw("CASE WHEN tbl_riesgo.IdEstatus = 1 then '' WHEN tbl_riesgo.IdEstatus = 2 then 'El numero de contrato no se encuentra' else 'No especificado' end as IdEstatus"));
			//return var_dump($lobjDiferencias->get());
			$lobjDataTable = Datatables::queryBuilder($lobjDiferencias)
			->make(true);

			return $lobjDataTable;

	}

	function postShowdiferencias(Request $request){

			$lobjMyCheckLaboral = new MyCheckLaboral();
			//$lobjDiferencias = $lobjMyCheckLaboral->getDiferencias();
			$lobjDiferencias = \DB::table('tbl_diferencias_calculo')
			                   ->where("periodo",$lobjMyCheckLaboral::getPeriodo())
			                   ->select('rut',
												          'nombre',
																	'cont_numero',
																	'cl_diferencia_pago',
																	\DB::raw("CASE WHEN tbl_diferencias_calculo.IdEstatus = 1 then '' WHEN tbl_diferencias_calculo.IdEstatus = 2 then 'El rut de la persona no se encuentra' WHEN tbl_diferencias_calculo.IdEstatus = 3 then 'El numero de contrato no se encuentra' else 'No especificado' end as IdEstatus"));
			//return var_dump($lobjDiferencias->get());
			$lobjDataTable = Datatables::queryBuilder($lobjDiferencias)
			->editColumn('cl_diferencia_pago', function ($lobjDocumentos) {
					return \MyFormats::FormatCurrency($lobjDocumentos->cl_diferencia_pago);
			})
			->make(true);

			return $lobjDataTable;

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

		$this->data['access']		= $this->access;
		return view('checklaboral.cargamasivadiferencias.form',$this->data);
	}

	public function postCarga(){

		$lobjFileLoad = Input::file("FileDataEmployee");

		$lobjMyCheckLaboral = new MyCheckLaboral();
		$larrResult = $lobjMyCheckLaboral::LoadDiferencias($lobjFileLoad);
	  echo json_encode($larrResult);

	}

	public function postCargancpersonas(){

		$lobjFileLoad = Input::file("FileDataNC");

		$lobjMyCheckLaboral = new MyCheckLaboral();
		$larrResult = $lobjMyCheckLaboral::LoadNoConformidades($lobjFileLoad);
	  echo json_encode($larrResult);

	}

	public function postCargancempresas(){

		$lobjFileLoad = Input::file("FileDataNCEmpresas");

		$lobjMyCheckLaboral = new MyCheckLaboral();
		$larrResult = $lobjMyCheckLaboral::LoadNoConformidadesEmpresa($lobjFileLoad);
	  echo json_encode($larrResult);

	}

	public function postCargariesgo(){

		$lobjFileLoad = Input::file("FileDataRiesgo");

		$lobjMyCheckLaboral = new MyCheckLaboral();
		$larrResult = $lobjMyCheckLaboral::LoadRiesgo($lobjFileLoad);
	  echo json_encode($larrResult);

	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('checklaboral.cargamasivadiferencias.view',$this->data);
	}

	function postSave( Request $request)
	{


	}

	public function postDelete( Request $request)
	{

		if($this->access['is_remove'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

	}

	function postCierre(Request $request){

		$larrResultado = array();
		$ldatPeriodo = $request->input('periodo');
		$lintAccion = $request->input('accion');

		if ($ldatPeriodo){
			$ldatPeriodo = \MyFormats::FormatoFecha('01/'.$ldatPeriodo);
		}else{
			return response()->json(array(
				'code'=>'0',
				'status'=>'error',
				'message'=> "Error, datos no esperados"
			));
		}
		
		$lobjMyCheckLaboral = new MyCheckLaboral();

		if ($lintAccion=="nuevo"){
			$larrResultado = $lobjMyCheckLaboral::Open($ldatPeriodo);
		}else if ($lintAccion=="reprocesar"){
			$larrResultado = $lobjMyCheckLaboral::ReOpen();
		}else if ($lintAccion=="eliminar")
		{
			$sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
			if($sitio->Valor=='Transbank')
				{
				$periodoActual = $lobjMyCheckLaboral::getPeriodo();
				\DB::table('tbl_f301_discrepancias')->where('periodo',$periodoActual)->delete();
				}
				$larrResultado = $lobjMyCheckLaboral::Delete();
		}

		$lobjMyCheckLaboral = new MyCheckLaboral();
		$ldatPeriodoActual = $lobjMyCheckLaboral::getPeriodo();
		$larrResultado['PeriodoActual'] = \MyFormats::FormatDate($ldatPeriodoActual,'m/Y');
		$larrResultado['PeriodoNuevo'] = \MyFormats::FormatDate(date('Y-m-d',strtotime($ldatPeriodoActual."+ 1 month")),'m/Y');

		return response()->json($larrResultado);

	}


}
