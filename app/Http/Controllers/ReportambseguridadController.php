<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportambseguridad;
use App\Models\Georeporte;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 


class ReportambseguridadController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'reportambseguridad';
	static $per_page	= '10';

	public function __construct()
	{
		
		parent::__construct();
		
		$this->model = new Reportambseguridad();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportambseguridad',
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
			return view('reportambseguridad.index',$this->data);
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
			
			return view('reportambseguridad.reportambseguridad',$this->data);
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

		$this->data['ind'] = 'acc';
		$lobjMyReports::setOptions(array("title"=>"Índice de frecuencia"));
		$lobjMyReports::setInformacion($this->data['ind']);
		$larrResult["data"][$this->data['ind']] = $lobjMyReports::getCharts("bar","global",null,null,1,1);
		
		$this->data['ind'] = 'gra';
		$lobjMyReports::setOptions(array("title"=>"Índice de Gravedad"));
		$lobjMyReports::setInformacion($this->data['ind']);
		$larrResult["data"][$this->data['ind']] = $lobjMyReports::getCharts("bar","global",null,null,1,1);
		
		$this->data['ind'] = 'fct';
		$lobjMyReports::setOptions(array("title"=>"Fiscalización condiciones de trabajo"));
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
		return view('reportambseguridad.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('reportambseguridad.view',$this->data);	
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