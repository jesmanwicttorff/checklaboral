<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportdetcontrato;
use App\Models\Georeporte;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 


class ReportdetcontratoController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'reportdetcontrato';
	static $per_page	= '10';

	public function __construct()
	{
		
		parent::__construct();
		
		$this->model = new Reportdetcontrato();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportdetcontrato',
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

		if (!$this->data['anterior']){
			$lobjMyReports = new \MyReports($this->data);
		    $larrFilters = $lobjMyReports::getFilters();
		    $larrInformacion = $lobjMyReports::getInformacion();
			$this->data['titulo'] = $larrInformacion['title'];
			$this->data	= array_merge($this->data,$larrFilters);
			$this->data["valores"] = $lobjMyReports::getGlobal("global",null,null,1, $this->data['id']);
			return view('reportdetcontrato.reportdetcontrato',$this->data);
		}

		$this->modeltwo = new Georeporte();
		$lobjInfoGeoReporte = $this->modeltwo->makeInfo( 'georeporte' );
 		$this->data['Campos'] = array();
 		foreach ($lobjInfoGeoReporte['config']['forms'] as $t) {
 			$this->data['Campos'][$t['field']] = \SiteHelpers::activeLang($t['field'],(isset($t['language'])? $t['language']: array()));
		}

		return view('reportdetcontrato.index',$this->data);
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
		$lstrResultado['filter'] = $lobjMyReports::getFilters();
		$lstrResultado['data'] = $lobjMyReports::getGlobal("global",null,null,1, $this->data['id']);

		return response()->json($lstrResultado);
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
		return view('reportdetcontrato.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('reportdetcontrato.view',$this->data);	
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