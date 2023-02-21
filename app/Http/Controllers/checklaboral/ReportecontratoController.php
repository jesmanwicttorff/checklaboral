<?php namespace App\Http\Controllers\checklaboral;

use App\Http\Controllers\controller;
use App\Library\MyCheckLaboral;
use App\Models\Centros;
use App\Models\checklaboral\Reportecontrato;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;


class ReportecontratoController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'reportecontrato';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();

		$this->model = new Reportecontrato();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportecontrato',
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

		$this->data['access']		= $this->access;

		return view('checklaboral.reportecontrato.index',$this->data);
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
		return view('checklaboral.reportecontrato.form',$this->data);
	}

	public function getShow( Request $request, $idfaena = null, $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$larrParametros = $request->input('parametros');

		//Vamos sumando más indicadores
		$lobjCheckLaboral = new MyCheckLaboral();
		$this->data = $lobjCheckLaboral->LoadData($larrParametros,null,$id);
		$this->data['Periodos'] = $lobjCheckLaboral->getPeriodos();
		$this->data['DataDesempenio'] = $lobjCheckLaboral->LoadDataDesempenio($larrParametros,null,$id);

		$sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
		if($sitio->Valor=='Transbank'){
			$this->data['Discrepancias'] = $lobjCheckLaboral->loadDiscrepancias($larrParametros,null,$id);
			$this->data['DiscrepanciasAcumuladas'] = $lobjCheckLaboral->loadDiscrepanciasAcumuladas($larrParametros,null,$id);
		}

	    $lobjCentros = Centros::find($idfaena);
	    $this->data['FaenaId'] = $idfaena;
		if ($lobjCentros){
			$this->data['Faena'] = $lobjCentros->Descripcion;
		}else{
			$this->data['Faena'] = "";
		}

    	$this->data['id']		= $id;

		$this->data['access']		= $this->access;
		return view('checklaboral.reportecontrato.index',$this->data);
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

	public function getReporte(Request $request, $pstrTipo = "reportecontrato"){

		$ldatPeriodo = $request->input('periodo');
		$lintContrato = $request->input('contrato');
		$lobjMyCheckLaboral = new MyCheckLaboral($ldatPeriodo);
		$lobjResult = $lobjMyCheckLaboral->downloadreport($pstrTipo, $lintContrato);
		if ($lobjResult['status']=="success"){
			return response()->download($lobjResult['result']);
		}else{
			return '<script>alert("No se encuentra el reporte");</script>';
		}

	}

}
