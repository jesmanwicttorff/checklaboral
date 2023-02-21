<?php namespace App\Http\Controllers\checklaboral;

use App\Http\Controllers\controller;
use App\Models\checklaboral\Reportedesempeno;
use App\Library\MyCheckLaboral;
use App\Models\Contratistas;
use App\Models\Centros;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;


class ReportedesempenoController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'reportedesempeno';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Reportedesempeno();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportedesempeno',
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


		return view('checklaboral.reportedesempeno.index',$this->data);
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
		return view('checklaboral.reportedesempeno.form',$this->data);
	}

	public function getShow( Request $request, $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

	  $larrParametros = $request->input('parametros');

		//Vamos sumando más indicadores
		$lobjCheckLaboral = new MyCheckLaboral();
		$this->data = $lobjCheckLaboral->LoadData($larrParametros,$id);

		$this->data['DataDesempenio'] = $lobjCheckLaboral->LoadDataDesempenio($larrParametros,$id);

		$lobjCentros = Centros::find($id);
		if ($lobjCentros){
			$this->data['Faena'] = $lobjCentros->Descripcion;
		}else{
			$this->data['Faena'] = "";
		}

    $this->data['id']		= $id;

		$this->data['access']		= $this->access;
		return view('checklaboral.reportedesempeno.index',$this->data);
	}

	public function getTransversal( Request $request, $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

	  	$larrParametros = $request->input('parametros');

		//Vamos sumando más indicadores
		$lobjCheckLaboral = new MyCheckLaboral();
		$this->data = $lobjCheckLaboral->LoadData($larrParametros,null,null,$id);

		$this->data['DataDesempenio'] = $lobjCheckLaboral->LoadDataDesempenio($larrParametros,null,null,$id);

		$lobjContratistas = Contratistas::find($id);
		if ($lobjContratistas){
			$this->data['Contratista'] = $lobjContratistas->RazonSocial;
		}else{
			$this->data['Contratista'] = "";
		}

    	$this->data['id']		= $id;

		$this->data['access']		= $this->access;
		return view('checklaboral.reportedesempeno.transversal',$this->data);
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
