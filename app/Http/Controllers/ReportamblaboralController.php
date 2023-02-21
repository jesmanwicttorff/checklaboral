<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportamblaboral;
use App\Models\Georeporte;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 


class ReportamblaboralController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'reportamblaboral';
	static $per_page	= '10';

	public function __construct()
	{
		
		parent::__construct();
		
		$this->model = new Reportamblaboral();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportamblaboral',
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

		if ($request->input('anterior')){
			return view('reportamblaboral.index',$this->data);
		}else{

			//asignamos las viriables
		    $this->data['reg'] = $request->input('reg');
		    $this->data['seg'] = $request->input('seg');
		    $this->data['area'] = $request->input('area');
		    $this->data['ind'] = $request->input('ind');
		    $this->data['rep'] = $request->input('rep');
		    $this->data['year'] = $request->input('year');
		    $this->data['mes'] = $request->input('mes');

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
			

			return view('reportamblaboral.reportamblaboral',$this->data);
		}

	}	

	public function postLoadinfo(Request $request ){
		
		//asignamos las viriables
	    $this->data['reg'] = $request->input('reg');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['area'] = $request->input('area');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');

	    $lobjMyReports = new \MyReports($this->data);
	    $larrFilters = $lobjMyReports::getFilters();
	    $larrResult['filter'] = $lobjMyReports::getFilters();
		$this->data	= array_merge($this->data,$larrFilters);

		$this->data['ind'] = 'obl';
		$lobjMyReports::setOptions(array("title"=>"% Empresas con Obligaciones Laborales no cubiertas"));
		$lobjMyReports::setInformacion($this->data['ind']);
		$larrResult["data"][$this->data['ind']] = $lobjMyReports::getCharts("bar","global",null,null,1,1);
		
		$this->data['ind'] = 'mit';
		$lobjMyReports::setOptions(array("title"=>"% de contratos de empresas con multas"));
		$lobjMyReports::setInformacion($this->data['ind']);
		$larrResult["data"][$this->data['ind']] = $lobjMyReports::getCharts("bar","global",null,null,1,1);
		
		$this->data['ind'] = 'fla';
		$lobjMyReports::setOptions(array("title"=>"Fiscalización Laboral"));
		$lobjMyReports::setInformacion($this->data['ind']);
		$larrResult["data"][$this->data['ind']] = $lobjMyReports::getCharts("bar","global",null,null,1,1);

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
		return view('reportamblaboral.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('reportamblaboral.view',$this->data);	
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