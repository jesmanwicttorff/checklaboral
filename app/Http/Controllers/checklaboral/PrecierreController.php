<?php namespace App\Http\Controllers\checklaboral;

use App\Http\Controllers\controller;
use App\Library\MyCheckLaboral;
use App\Models\checklaboral\Precierre;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;


class PrecierreController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'precierre';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();

		$this->model = new Precierre();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'precierre',
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

		//Vamos sumando más indicadores
		$lobjCheckLaboral = new MyCheckLaboral();
		$ldatFechaPeriodo = $lobjCheckLaboral->getPeriodo();

		$this->data['periodo'] = $ldatFechaPeriodo;

		return view('checklaboral.precierre.index',$this->data);
	}

	function postCierre(Request $request){

		$ldatPeriodo = $request->input('periodo');
		if ($ldatPeriodo){
			$ldatPeriodo = \MyFormats::FormatoFecha('01/'.$ldatPeriodo);
		}else{
			return response()->json(array(
				'code'=>'0',
				'status'=>'error',
				'message'=> "Error, datos no esperados"
			));
		}
		$lintReproceso = $request->input('reproceso');

		$lstrQuery = "SELECT fnCierreLaboral('".$ldatPeriodo."',".$lintReproceso.") as Resultado from dual;";
		$lobjResultado = \DB::select($lstrQuery);

		if ($lobjResultado){
			$lstrResultado = $lobjResultado[0]->Resultado;
			$larrResultado = explode("|",$lstrResultado);
			$lintCodigo = $larrResultado[0];
			$lstrResultado = $larrResultado[1];
		}else{
			$lintCodigo = 0;
			$lstrResultado = "Error";
		}

		return response()->json(array(
			'code'=>$lintCodigo,
			'status'=>'success',
			'message'=> $lstrResultado
		));

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
		return view('checklaboral.precierre.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('checklaboral.precierre.view',$this->data);
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
