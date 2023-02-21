<?php namespace App\Http\Controllers\checklaboral;

use App\Http\Controllers\controller;
use App\Library\MyCheckLaboral;
use App\Models\checklaboral\Reporte;
use App\Models\Centros;
use App\Models\Contratistas;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;


class ReporteController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'reporte';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();

		$this->model = new Reporte();
		$this->info = $this->model->makeInfo($this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reporte',
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

		$larrParametros = $request->input('parametros');

		//Vamos sumando más indicadores
		$lobjCheckLaboral = new MyCheckLaboral();
		$this->data = $lobjCheckLaboral->LoadData($larrParametros);
		$this->data['Periodos'] = $lobjCheckLaboral->getPeriodos();

		if (intval(count($this->data['ListadoContratos']))==1){
			return Redirect::to('checklaboral/reportefaena/show/'.$this->data['ListadoContratos'][0]->IdFaena);
		}
		
		$this->data['access']		= $this->access;

		return view('checklaboral.reporte.index',$this->data);
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
		return view('checklaboral.reporte.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('checklaboral.reporte.view',$this->data);
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

}
