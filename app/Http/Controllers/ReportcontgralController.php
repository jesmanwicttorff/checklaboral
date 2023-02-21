<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportcontgral;
use App\Models\Georeporte;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 


class ReportcontgralController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'reportcontgral';
	static $per_page	= '10';

	public function __construct()
	{
		
		parent::__construct();
		
		$this->model = new Reportcontgral();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportcontgral',
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
	    $this->data['rep'] = $request->input('rep');
	    $this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');

	    $lobjMyReports = new \MyReports($this->data);
	    $larrFilters = $lobjMyReports::getFilters();
		$this->data	= array_merge($this->data,$larrFilters);
		
		$this->modeltwo = new Georeporte();
		$lobjInfoGeoReporte = $this->modeltwo->makeInfo( 'georeporte' );
 		$this->data['Campos'] = array();
 		foreach ($lobjInfoGeoReporte['config']['forms'] as $t) {
 			$this->data['Campos'][$t['field']] = \SiteHelpers::activeLang($t['field'],(isset($t['language'])? $t['language']: array()));
		}

		return view('reportcontgral.reportcontgral',$this->data);
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

	    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');

	    $lobjContratos = \DB::table('tbl_contrato')
	    ->select("tbl_contrato.contrato_id","tbl_contratistas.Rut", "tbl_contratistas.RazonSocial", "tbl_contrato.cont_numero", "tbl_contrato.cont_nombre")
	    ->join("tbl_contratistas", "tbl_contrato.IdContratista","=","tbl_contratistas.IdContratista")
	    ->join("dim_tiempo","tbl_contrato.cont_FechaFin","=","dim_tiempo.fecha")
	    ->where("controlareporte","=",1);

	    if ($lintLevelUser==6) {
      		$lobjContratos = $lobjContratos->where("tbl_contrato.entry_by_access","=",$lintIdUser);
  		}else if ($lintLevelUser==4) {
  			$lobjContratos = $lobjContratos->where("tbl_contrato.admin_id","=",$lintIdUser);
  		}

  		$lobjContratos = $lobjContratos->get();

	    $lobjMyReports = new \MyReports($this->data);
		$lstrResultado['filter'] = $lobjMyReports::getFilters();
		$lstrRender = "";
		$lstrLink = "";
		$lintContador = "";
	    foreach ($lobjContratos as $larrContratos) {
	    	//$lstrRender .= "<tr>";
	    	if ($lintContador>1 || $lintContador===""){
	    		if ($lintContador!==""){
	    			$lstrRender .= '</div>';
	    		}
	    		$lintContador = "";
	    		$lstrRender .= '<div class="row">';
	    	}
	    	$lintContador = $lintContador===""?0:$lintContador+1;
	    	$lintResultado = $lobjMyReports::getGlobal("global",null,null,1, $larrContratos->contrato_id);
	    	$lintResultado = $lintResultado["general"];
	    	$lstrResultado['data'][] = array("rut"=>$larrContratos->Rut, 
	    		                             "razonsocial"=>$larrContratos->RazonSocial, 
	    		                             "cont_numero"=>$larrContratos->cont_numero,
	    		                             "cont_nombre"=>$larrContratos->cont_nombre,
	    		                             "general"=>$lintResultado);

	    	//$lstrRender .= '<td>';
	    	$lstrRender .= '<div class="col-md-4 linkcontrato" style="padding-top:5px; padding-bottom:5px;" >';
	    	$lstrRender .= '<table>';
	    	$lstrRender .= '<tr>';
	    	$lstrRender .= '<td style="width:28px;">';
	    	$lstrRender .= '<div class="circle" style="background-color:'.$lintResultado['color'].'; width:28px;">';
	    	$lstrRender .= $lintResultado["value"].'%';
	    	$lstrRender .= '</div>';
	    	$lstrRender .= '</td>';
	    	$lstrRender .= '<td style="padding-left:10px;">';
	    	$lstrRender .= '<a style="font-size:11px;" href="reportdetcontrato?id='.$larrContratos->contrato_id.'&amp;mes='.$this->data['mes'].'&amp;year='.$this->data['year'].'&amp;area='.$this->data['area'].'&amp;reg='.$this->data['reg'].'&amp;seg='.$this->data['seg'].'">';
	    	$lstrRender .= $larrContratos->cont_numero.' - '.$larrContratos->RazonSocial;
	    	$lstrRender .= '</a> ';
	    	$lstrRender .= '</td>';
	    	$lstrRender .= '</tr>';
	    	$lstrRender .= '</table>';
	    	$lstrRender .= '</div>';

	    }

	    if ($lintContador!=3 || $lintContador==""){

	    }

	    $lstrResultado['view'] = $lstrRender;

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
		return view('reportcontgral.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('reportcontgral.view',$this->data);	
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