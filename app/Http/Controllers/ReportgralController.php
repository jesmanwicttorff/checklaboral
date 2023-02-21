<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportgral;
use App\Models\Georeporte;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 


class ReportgralController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'reportgral';
	static $per_page	= '10';

	public function __construct()
	{
		
		parent::__construct();
		
		$this->model = new Reportgral();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportgral',
			'return'	=> self::returnUrl()
			
		);
		\App::setLocale(CNF_LANG);
		if (defined('CNF_MULTILANG') && CNF_MULTILANG == '1') {
		
		$lang = (\Session::get('lang') != "" ? \Session::get('lang') : CNF_LANG);
		\App::setLocale($lang);
		}  
		
		
	}

	public function getDownload( Request $request ) {
		
		$pintyear = $request->input('year');
		$pintmonth = $request->input('month');
		$pintreg = $request->input('reg');
		$pintarea = $request->input('area');
		$pintseg = $request->input('seg');
		$pincoment = $request->input('coment');
		//---
		$pstrregdesc = $request->input('regdesc');
		$pstrareadesc = $request->input('areadesc');
		$pstrsegdesc = $request->input('segdesc');
		//---
		$pintIdContrato = $request->input('idcontrato');
		$pintIdContratista = $request->input('idcontratista');

		//recuperamos las descripciones de los filtros


		$larrData['DataReporte'] = array("reg"=>$pintreg,
			                     'regdesc'=> $pstrregdesc,
					             'seg' => $pintseg,
					             'segdesc'=> $pstrsegdesc,
					             'area' => $pintarea,
					             'areadesc'=> $pstrareadesc,
					             'ind' => null,
					             'year' => $pintyear,
					             'month' => $pintmonth,
                                'coment' => $pincoment,
					             'id'=>null              
    	);
    	$lobjMyReports = new \MyReports($larrData['DataReporte']);
    	$larrDataRender = $lobjMyReports::GenerateReport($pintyear, $pintmonth, $pintreg, $pintarea, $pintseg, $pstrregdesc, $pstrareadesc, $pstrsegdesc, $pintIdContrato, $pintIdContratista, $pincoment);

    	header('Set-Cookie: fileDownload=true; path=/');
		header('Cache-Control: max-age=60, must-revalidate');
		header("Content-type: application/pdf");
		header('Content-Disposition: attachment; filename="Reporte_general_'.date('Ymd_His').'.pdf"');
		
		echo $larrDataRender;

	}

	public  function getPreview( Request $request ){
        $pintyear = $request->input('year');
        $pintmonth = $request->input('month');
        $pintreg = $request->input('reg');
        $pintarea = $request->input('area');
        $pintseg = $request->input('seg');
        $pincoment = $request->input('coment');
        //---
        $pstrregdesc = $request->input('regdesc');
        $pstrareadesc = $request->input('areadesc');
        $pstrsegdesc = $request->input('segdesc');
        //---
        $pintIdContrato = $request->input('idcontrato');
        $pintIdContratista = $request->input('idcontratista');

        //recuperamos las descripciones de los filtros


        $larrData['DataReporte'] = array("reg"=>$pintreg,
            'regdesc'=> $pstrregdesc,
            'seg' => $pintseg,
            'segdesc'=> $pstrsegdesc,
            'area' => $pintarea,
            'areadesc'=> $pstrareadesc,
            'ind' => null,
            'year' => $pintyear,
            'month' => $pintmonth,
            'id'=>null
        );
        $lobjMyReports = new \MyReports($larrData['DataReporte']);
        $larrDataRender = $lobjMyReports::GeneratePreview($pintyear, $pintmonth, $pintreg, $pintarea, $pintseg, $pstrregdesc, $pstrareadesc, $pstrsegdesc, $pintIdContrato, $pintIdContratista, $pincoment);


        echo $larrDataRender;
    }
	
	public function getIndex( Request $request )
	{

		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

			if ($request->input('anterior')){
			    return view('reportgral.index',$this->data);
			}

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
		    $larrInformacion = $lobjMyReports::getInformacion();
			$this->data['titulo'] = $larrInformacion['title'];
			$this->data	= array_merge($this->data,$larrFilters);
			$this->data["valores"] = $lobjMyReports::getGlobal("global",null,null,1);

			$this->data["ljsnDataReporte"] = json_encode($this->data["valores"]);

			$this->modeltwo = new Georeporte();
			$lobjInfoGeoReporte = $this->modeltwo->makeInfo( 'georeporte' );
	 		$this->data['Campos'] = array();
	 		foreach ($lobjInfoGeoReporte['config']['forms'] as $t) {
	 			$this->data['Campos'][$t['field']] = \SiteHelpers::activeLang($t['field'],(isset($t['language'])? $t['language']: array()));
			}
			
			return view('reportgral.reportgral',$this->data);
		
	}	

	public function postLoadinfo(Request $request ){
		
		//asignamos las viriables
	    $this->data['reg'] = $request->input('reg');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['area'] = $request->input('area');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');

	    $lobjMyReports = new \MyReports($this->data);
		$lstrResultado['filter'] = $lobjMyReports::getFilters();
		$lstrResultado['data'] = $lobjMyReports::getGlobal("global",null,null,1);

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
		return view('reportgral.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('reportgral.view',$this->data);	
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