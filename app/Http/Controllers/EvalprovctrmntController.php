<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Evalprovctrmnt;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;


class EvalprovctrmntController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'evalprovctrmnt';
	static $per_page	= '10';

	public function __construct()
	{

		parent::__construct();
		
		$this->model = new Evalprovctrmnt();

		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'evalprovctrmnt',
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

		$sort = (!is_null($request->input('sort')) ? $request->input('sort') : 'id_evaluacion');
		$order = (!is_null($request->input('order')) ? $request->input('order') : 'asc');
		// End Filter sort and order for query
		// Filter Search for query
		$filter = '';
		if(!is_null($request->input('search')))
		{
			$search = 	$this->buildSearch('maps');
			$filter = $search['param'];
			$this->data['search_map'] = $search['maps'];
		}


		$page = $request->input('page', 1);
		$params = array(
			'page'		=> $page ,
			'limit'		=> (!is_null($request->input('rows')) ? filter_var($request->input('rows'),FILTER_VALIDATE_INT) : static::$per_page ) ,
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query
		$results = $this->model->getRows( $params );

		// Build pagination setting
		$page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
		$pagination = new Paginator($results['rows'], $results['total'], $params['limit']);
		$pagination->setPath('evalprovctrmnt');

		$this->data['rowData']		= $results['rows'];
		// Build Pagination
		$this->data['pagination']	= $pagination;
		// Build pager number and append current param GET
		$this->data['pager'] 		= $this->injectPaginate();
		// Row grid Number
		$this->data['i']			= ($page * $params['limit'])- $params['limit'];
		// Grid Configuration
		$this->data['tableGrid'] 	= $this->info['config']['grid'];
		$this->data['tableForm'] 	= $this->info['config']['forms'];
		$this->data['colspan'] 		= \SiteHelpers::viewColSpan($this->info['config']['grid']);
		// Group users permission
		$this->data['access']		= $this->access;
		// Detail from master if any
		$this->data['fields'] =  \AjaxHelpers::fieldLang($this->info['config']['grid']);
		// Master detail link if any
		$this->data['subgrid']	= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
		// Render into template
		return view('evalprovctrmnt.index',$this->data);
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

		$row = $this->model->find($id);
		if($row)
		{
			$this->data['row'] =  $row;
		} else {
			$this->data['row'] = $this->model->getColumnTable('tbl_evalcontratistas');
		}
		$this->data['fields'] =  \AjaxHelpers::fieldLang($this->info['config']['forms']);

		$this->data['id'] = $id;
		return view('evalprovctrmnt.form',$this->data);
	}

	public function getShow( Request $request, $id = null)
	{

		if($this->access['is_detail'] ==0)
		return Redirect::to('dashboard')
			->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$row = $this->model->getRow($id);
		if($row)
		{
			$this->data['row'] =  $row;
			$this->data['fields'] 		=  \SiteHelpers::fieldLang($this->info['config']['grid']);
			$this->data['id'] = $id;
			$this->data['access']		= $this->access;
			$this->data['subgrid']	= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
			$this->data['fields'] =  \AjaxHelpers::fieldLang($this->info['config']['grid']);
			return view('evalprovctrmnt.view',$this->data);
		} else {
			return Redirect::to('evalprovctrmnt')->with('messagetext','Record Not Found !')->with('msgstatus','error');
		}
	}

	function postCopy( Request $request)
	{
	    foreach(\DB::select("SHOW COLUMNS FROM tbl_evalcontratistas ") as $column)
        {
			if( $column->Field != 'id_evaluacion')
				$columns[] = $column->Field;
        }

		if(count($request->input('ids')) >=1)
		{
			$toCopy = implode(",",$request->input('ids'));
			$sql = "INSERT INTO tbl_evalcontratistas (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_evalcontratistas WHERE id_evaluacion IN (".$toCopy.")";
			\DB::select($sql);
			return Redirect::to('evalprovctrmnt')->with('messagetext',\Lang::get('core.note_success'))->with('msgstatus','success');
		} else {

			return Redirect::to('evalprovctrmnt')->with('messagetext','Please select row to copy')->with('msgstatus','error');
		}

	}

	function postSave( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);


		if ($validator->passes()) {
			//$data = $this->validatePost('tb_evalprovctrmnt');
			$data = $this->validatePost('tbl_evalcontratistas');

			$data['eval_fecha'] = date('Y-m-d',strtotime('01-'.str_replace('/','-',$data['eval_fecha'])));

			$lobjContrato = \DB::table('tbl_contrato')
				->select('tbl_contrato.cont_fechainicio', 'tbl_contrato.cont_fechafin')
				->where('contrato_id','=', $request->input('contrato_id'))
				->first();

			if ($data['eval_fecha'] > date('Y-m-d') ) {
					return response()->json(array(
						'message'	=> 'No se puede crear registro con fechas futuras',
						'status'	=> 'error'
					));
				}else if ($data['eval_fecha'] > $lobjContrato->cont_fechafin){
					return response()->json(array(
						'message'	=> 'No se puede crear registro con fechas posterior a la fecha de vigencia del contrato',
						'status'	=> 'error'
					));
				}else if ($data['eval_fecha'] < $lobjContrato->cont_fechainicio){
					return response()->json(array(
						'message'	=> 'No se puede crear registro con fechas anterior a la fecha de inicio del contrato',
						'status'	=> 'error'
					));
				}
				if  (strlen($request->input('id_evaluacion'))==0){
				$lobjEvalC = \DB::table("tbl_evalcontratistas")
									->where("contrato_id","=",$request->input('contrato_id'))
									->where("eval_fecha","=",$data['eval_fecha'])
									->get();
				}else{
					$lobjEvalC = \DB::table("tbl_evalcontratistas")
					->where("contrato_id","=",$request->input('contrato_id'))
					->where("eval_fecha","=",$data['eval_fecha'])
					->where("id_evaluacion","!=",$data['id_evaluacion'])
					->get();
				}
				if ($lobjEvalC){
					return response()->json(array(
						'message'	=> 'Ya existe un registro creado para el periodo seleccionado',
						'status'	=> 'error'
					));
				}

			$id = $this->model->insertRow($data , $request->input('id_evaluacion'));

			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success')
				));

		} else {

			$message = $this->validateListError(  $validator->getMessageBag()->toArray() );
			return response()->json(array(
				'message'	=> $message,
				'status'	=> 'error'
			));
		}

	}

	public function postDelete( Request $request)
	{

		if($this->access['is_remove'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
		// delete multipe rows
		if(count($request->input('ids')) >=1)
		{
			$this->model->destroy($request->input('ids'));

			\SiteHelpers::auditTrail( $request , "ID : ".implode(",",$request->input('ids'))."  , Has Been Removed Successfull");
			// redirect
			return Redirect::to('evalprovctrmnt')
        		->with('messagetext', \Lang::get('core.note_success_delete'))->with('msgstatus','success');

		} else {
			return Redirect::to('evalprovctrmnt')
        		->with('messagetext','No Item Deleted')->with('msgstatus','error');
		}

	}

	public static function display( )
	{
		$mode  = isset($_GET['view']) ? 'view' : 'default' ;
		$model  = new Evalprovctrmnt();
		$info = $model::makeInfo('evalprovctrmnt');

		$data = array(
			'pageTitle'	=> 	$info['title'],
			'pageNote'	=>  $info['note']

		);

		if($mode == 'view')
		{
			$id = $_GET['view'];
			$row = $model::getRow($id);
			if($row)
			{
				$data['row'] =  $row;
				$data['fields'] 		=  \SiteHelpers::fieldLang($info['config']['grid']);
				$data['id'] = $id;
				return view('evalprovctrmnt.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'id_evaluacion' ,
				'order'		=> 'asc',
				'params'	=> '',
				'global'	=> 1
			);

			$result = $model::getRows( $params );
			$data['tableGrid'] 	= $info['config']['grid'];
			$data['rowData'] 	= $result['rows'];

			$page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
			$pagination = new Paginator($result['rows'], $result['total'], $params['limit']);
			$pagination->setPath('');
			$data['i']			= ($page * $params['limit'])- $params['limit'];
			$data['pagination'] = $pagination;
			return view('evalprovctrmnt.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_evalcontratistas');
			 $this->model->insertRow($data , $request->input('id_evaluacion'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}




}
