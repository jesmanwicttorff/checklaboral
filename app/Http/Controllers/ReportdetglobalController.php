<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportdetglobal;
use App\Models\Georeporte;
use Illuminate\Http\Request;
use App\Library\MyReports;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 


class ReportdetglobalController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'reportdetglobal';
	static $per_page	= '10';

	public function __construct()
	{
		
		parent::__construct();
		
		$this->model = new Reportdetglobal();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportdetglobal',
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

		if (!$request->input('anterior') && ($request->input('ind') == 'kpi' 
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
			 
			)){ 

			//asignamos las viriables
		    $this->data['reg'] = $request->input('reg');
		    $this->data['seg'] = $request->input('seg');
		    $this->data['area'] = $request->input('area');
		    $this->data['ind'] = $request->input('ind');
		    $this->data['rep'] = $request->input('rep');
		    $this->data['year'] = $request->input('year');
		    $this->data['mes'] = $request->input('mes');
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

			if (!$this->data['anterior']){
					$larrInformacion = $lobjMyReports::getInformacion();
					$this->data['titulo'] = $larrInformacion['title'];
					$this->data['unid'] = $larrInformacion['unid'];
					$this->data['GlobalIndicador'] = $lobjMyReports::getIndicadorGlobal($this->data['year'],$this->data['mes'],1);
				if ($request->input('ind') == 'evp'){
					return view('reportdetglobal.repevalcon',$this->data);
				}elseif ($request->input('ind') == 'kpi'){
					return view('reportdetglobal.repkpi',$this->data);
				}elseif ($request->input('ind') == 'adm'){
					return view('reportdetglobal.repevaladm',$this->data);
				}elseif ($request->input('ind') == 'esf'){
					return view('reportdetglobal.repesf',$this->data);
				}elseif ($request->input('ind') == 'dym'){
					return view('reportdetglobal.reptri',$this->data);
				}elseif ($request->input('ind') == 'tri'){
					return view('reportdetglobal.reptri',$this->data);
				}elseif ($request->input('ind') == 'gar'){
					return view('reportdetglobal.repgar',$this->data);
				}elseif ($request->input('ind') == 'obl'){
					return view('reportdetglobal.repobl',$this->data);
				}elseif ($request->input('ind') == 'mit'){
					return view('reportdetglobal.repmit',$this->data);
				}elseif ($request->input('ind') == 'fla'){
					return view('reportdetglobal.repfla',$this->data);
				}elseif ($request->input('ind') == 'acc'){
					return view('reportdetglobal.repacc',$this->data);
				}elseif ($request->input('ind') == 'gra'){
					return view('reportdetglobal.repgra',$this->data);
				}elseif ($request->input('ind') == 'fct'){
					return view('reportdetglobal.repfct',$this->data);
				}else{
					return view('reportdetglobal.index',$this->data);				
				}
			}

		}

		if ($request->input('ind')=='fin'){
			if ( defined('CNF_MODULO_FISICO') && CNF_MODULO_FISICO == 1){
				return view('reportdetavafis.indexfisico',$this->data);
			}else{
				return view('reportdetavafis.index',$this->data);
			}
		}
		
		return view('reportdetglobal.index',$this->data);
	}	

	function postLoadinfo(Request $request, $id = null){

		//asignamos las viriables
	    $this->data['reg'] = $request->input('reg');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['area'] = $request->input('area');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['id'] = $request->input('id');

	    $lobjMyReports = new \MyReports($this->data);
		$lobjMyReports::setOptions(array("title"=>"")); 
        $larrResult["filter"] = $lobjMyReports::getFilters(); 
        $larrResult["indicadorglobal"] = $lobjMyReports::getIndicadorGlobal(null,null,1); 
        $larrResult["chart"] = $lobjMyReports::getCharts("line","resumen",null,0,1,1); 
        return response()->json($larrResult); 
        
	}

	function postLoadinfodispersion(Request $request, $id = null){

		//asignamos las viriables
		$this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['reg'] = $request->input('reg');
	    $this->data['area'] = $request->input('area');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');

	    $lobjMyReports = new \MyReports($this->data);
	    $lobjMyReports::setOptions(array("title"=>""));
	    $larrResult = $lobjMyReports::getCharts("bubble","detalle",null,$this->data['mes'],0,1);
	    return response()->json($larrResult);
		
	}

	function postLoadinfocolumna(Request $request, $id = null){


		//asignamos las viriables
		$this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['reg'] = $request->input('reg');
	    $this->data['area'] = $request->input('area');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');

	    $lobjMyReports = new \MyReports($this->data);
	    $lobjMyReports::setOptions(array("title"=>""));
	    $larrResult = $lobjMyReports::getCharts("column","global",null,null,1,1);
	    return response()->json($larrResult);
		
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
		return view('reportdetglobal.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('reportdetglobal.view',$this->data);	
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