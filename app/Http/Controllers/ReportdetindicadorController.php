<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportdetindicador;
use App\Models\Georeporte;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 


class ReportdetindicadorController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'reportdetindicador';
	static $per_page	= '10';

	public function __construct()
	{
	
		parent::__construct();	
		
		$this->model = new Reportdetindicador();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportdetindicador',
			'return'	=> self::returnUrl()
			
		);
		\App::setLocale(CNF_LANG);
		if (defined('CNF_MULTILANG') && CNF_MULTILANG == '1') {

		$lang = (\Session::get('lang') != "" ? \Session::get('lang') : CNF_LANG);
		\App::setLocale($lang);
		}  
		
		
	}

	public function getIndex( Request $request )
	{

		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

			if ($request->input('ind') == 'kpi' 
			 || $request->input('ind') == 'evp' 
			 || $request->input('ind') == 'adm'
			 || $request->input('ind') == 'esf'
			 || $request->input('ind') == 'dym'
			 || $request->input('ind') == 'tri'
			 || $request->input('ind') == 'adm'
			 || $request->input('ind') == 'dym'
			 || $request->input('ind') == 'tri'
			 || $request->input('ind') == 'gar'
			 || $request->input('ind') == 'obl'
			 || $request->input('ind') == 'mit'
			 || $request->input('ind') == 'fla'
			 || $request->input('ind') == 'acc'
			 || $request->input('ind') == 'gra'
			 || $request->input('ind') == 'fct'
		){



		//asignamos las viriables
	    $this->data['reg'] = $request->input('reg');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['area'] = $request->input('area');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['id'] = $request->input('id');
	    $this->data['anterior'] = $request->input('anterior');

	    $lobjMyReports = new \MyReports($this->data);
	    $larrFilters = $lobjMyReports::getFilters();
		$this->data	= array_merge($this->data,$larrFilters);

		$this->modeltwo = new Georeporte();
		$lobjInfoGeoReporte = $this->modeltwo->makeInfo( 'georeporte' );
		//Recuperamos las etiquetas de los campos
 		$this->data['Campos'] = array();
 		foreach ($lobjInfoGeoReporte['config']['forms'] as $t) {
 			$this->data['Campos'][$t['field']] = \SiteHelpers::activeLang($t['field'],(isset($t['language'])? $t['language']: array()));
		}

		//Evaluacion del proveedor
		if (!$this->data['anterior']){
			$larrInformacion = $lobjMyReports::getInformacion();
			$this->data['titulo'] = $larrInformacion['title'];
			$this->data['GlobalIndicador'] = $lobjMyReports::getIndicadorGlobal($this->data['year'],$this->data['mes'],1,$this->data['id']);
			if ($request->input('ind') == 'kpi'){
				return view('reportdetindicador.repkpi',$this->data);
			}elseif ($request->input('ind') == 'evp'){
				return view('reportdetindicador.repevalcon',$this->data);
			}elseif ($request->input('ind') == 'adm'){
				return view('reportdetindicador.repevaladm',$this->data);
			}elseif ($request->input('ind') == 'esf'){
				return view('reportdetindicador.repesf',$this->data);
			}elseif ($request->input('ind') == 'dym'){
				return view('reportdetindicador.reptri',$this->data);
			}elseif ($request->input('ind') == 'tri'){
				return view('reportdetindicador.reptri',$this->data);
			}elseif ($request->input('ind') == 'gar'){
				return view('reportdetindicador.repgar',$this->data);
			}elseif ($request->input('ind') == 'obl'){
				return view('reportdetindicador.repobl',$this->data);
			}elseif ($request->input('ind') == 'mit'){
				return view('reportdetindicador.repmit',$this->data);
			}elseif ($request->input('ind') == 'fla'){
				return view('reportdetindicador.repfla',$this->data);
			}elseif ($request->input('ind') == 'acc'){
				return view('reportdetindicador.repacc',$this->data);
			}elseif ($request->input('ind') == 'gra'){
				return view('reportdetindicador.repgra',$this->data);
			}elseif ($request->input('ind') == 'fct'){
				return view('reportdetindicador.repfct',$this->data);
			}else{
				return view('reportdetindicador.index',$this->data);
			}
		}
		}
		
		return view('reportdetindicador.index',$this->data);

	}	

	function postLoadinfo(Request $request, $id = null){

		//asignamos las viriables
		$this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['reg'] = $request->input('reg');
	    $this->data['area'] = $request->input('area');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['id'] = $request->input('id');

	    $lobjMyReports = new \MyReports($this->data);
	    $lobjMyReports::setOptions(array("title"=>""));
	    $larrResult["filter"] = $lobjMyReports::getFilters();
	    $larrResult["indicadorglobal"] = $lobjMyReports::getIndicadorGlobal(null,null,1,$this->data['id']);
	    $larrResult["chart"] = $lobjMyReports::getCharts("line","resumen",null,0,1,1,$this->data['id']);
	    return response()->json($larrResult);

	}

	function getLoaddetalle (Request $request, $id = null) {

		//asignamos las viriables
		$this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['reg'] = $request->input('reg');
	    $this->data['area'] = $request->input('area');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['id'] = $request->input('id');

	    $lobjMyReports = new \MyReports($this->data);
	    if ($request->input('ind') == 'kpi'){
			$lobjDetalle = $lobjMyReports::getModelKPIDetalle(1,$this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'evp'){
			$lobjDetalle = $lobjMyReports::getModelEvaluacionProveedor(1, $this->data['year'],$this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'adm'){
			$lobjDetalle = $lobjMyReports::getModelEvaluacionAdministracion(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'esf'){
			$lobjDetalle = $lobjMyReports::getModelEvalEstadoFinanciero(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'dym'){
			$lobjDetalle = $lobjMyReports::getModelDeudaYMorosidad(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'tri'){
			$lobjDetalle = $lobjMyReports::getModelSituacionTributaria(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'gar'){
			$lobjDetalle = $lobjMyReports::getModelGarantiasYRespaldos(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'obl'){
			$lobjDetalle = $lobjMyReports::getModelObligacionesLaborales(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'mit'){
			$lobjDetalle = $lobjMyReports::getModelMultasInspeccionTrabajo(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'fla'){
			$lobjDetalle = $lobjMyReports::getModelFiscalizacionLaboral(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'acc'){
			$lobjDetalle = $lobjMyReports::getModelIndiceFrecuencia(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'gra'){
			$lobjDetalle = $lobjMyReports::getModelIndiceGravedad(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}elseif ($request->input('ind') == 'fct'){
			$lobjDetalle = $lobjMyReports::getModelCondicionesLaborales(1, $this->data['year'], $this->data['mes'],0,$this->data['id']);
		}else{
			return view('reportdetindicador.index',$this->data);
		}

	    $larrResult = array();

	    $lobjDetalle = $lobjDetalle->get();
		foreach ($lobjDetalle as $larrDetalle) {
			if ($request->input('ind') == 'kpi'){
				$larrDetalle->Puntaje = floatval($larrDetalle->Puntaje);
				$larrDetalle->Resultado = \MyFormats::FormatNumber(floatval($larrDetalle->Resultado))." %";
				$larrDetalle->ResultadoAjustado = \MyFormats::FormatNumber(floatval($larrDetalle->ResultadoAjustado))." %";
			}else{
				if (isset($larrDetalle->Meta)){
					$larrDetalle->Meta = floatval($larrDetalle->Meta);
				}else{
					$larrDetalle->{'Meta'} = 0;
				}
				$larrDetalle->Valor = floatval($larrDetalle->Valor);
			}
			$larrResult[] = $larrDetalle;
		}
		$larrResult = $lobjDetalle;
	    echo json_encode(array("data"=>$larrResult));

	}

	function getLoadcomentario (Request $request, $id = null) {

		//asignamos las viriables
		$this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['reg'] = $request->input('reg');
	    $this->data['area'] = $request->input('area');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['id'] = $request->input('id');
	    
	    $lobjMyReports = new \MyReports($this->data);
	    $lobjEvaluacionAdministrador = $lobjMyReports::getComentarios($this->data['id'], null, null,0,$this->data['id']);
	    $larrResult = array();
	    
	    $lobjEvaluacionAdministrador = $lobjEvaluacionAdministrador->get();
		$larrResult = $lobjEvaluacionAdministrador;
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
				
		$this->data['access']		= $this->access;
		return view('reportdetindicador.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('reportdetindicador.view',$this->data);	
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


}