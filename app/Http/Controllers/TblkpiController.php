<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Tblkpi;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class TblkpiController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'tblkpi';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Tblkpi();

		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'tblkpi',
			'pageUrl'			=>  url('tblkpi'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('tblkpi.index',$this->data);
	}

	public function postData( Request $request)
	{
		$params = array(
			'page'		=> '' ,
			'sort'		=> '' ,
			'order'		=> '',
			'params'	=> '',
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		$this->data['tableGrid'] 	= $this->info['config']['grid'];
		$this->data['access']		= $this->access;
		$this->data['setting'] 		= $this->info['setting'];
		return view('tblkpi.table',$this->data);

	}

	public function getShowlist( Request $request){

		$lintIdUser = \Session::get('uid');
		$lintGroupUser = \MySourcing::GroupUser($lintIdUser);
		$lintLevelUser = \MySourcing::LevelUser($lintIdUser);
		$sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
		$filter = "";

        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
        $filter .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';

		if(!is_null($request->input('search')))
		{
			$search = 	$this->buildSearch('maps');
			$filter = $search['param'];
			$this->data['search_map'] = $search['maps'];
		}
		$page = $request->input('page', 1);
		$params = array(
			'page'		=> $page ,
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query
		$results = $this->model->getRows( $params );
		#var_dump($results);
		$larrResult = array();
		$larrResultTemp = array();
		$i = 0;


		foreach ($results['rows'] as $row) {

			$id = $row->IdKpi;
			$larrResultTemp = array('id'=> ++$i,
								    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$id.'" /> '
								    );
			foreach ($this->info['config']['grid'] as $field) {
				if($field['view'] =='1') {
					$limited = isset($field['limited']) ? $field['limited'] :'';
					if (\SiteHelpers::filterColumn($limited )){
						$value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
						$larrResultTemp[$field['field']] = $value;
					}
				}
			}
			$larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('tblkpi/update/'.$id).'" onclick="ajaxViewDetail(\'#tblkpi\',this.href); return false;"  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-edit"></i></a></div>';
			$larrResult[] = $larrResultTemp;
		}

		echo json_encode(array("data"=>$larrResult));

	}

	function getUpdate(Request $request, $id = null)
	{

		$lintIdUser = \Session::get('uid');
		$lintLevelUser = \MySourcing::LevelUser($lintIdUser);

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
			$this->data['row'] 		=  $row;
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_kpis');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);

		$this->data['id'] = $id;

        //Contratos
	    $this->data['selectContratos'] = \DB::table('tbl_contrato')
	    ->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista")
	    ->select(\DB::raw('tbl_contrato.contrato_id as value'), \DB::raw("concat(tbl_contratistas.RUT, ' ', tbl_contratistas.RazonSocial,' - ',tbl_contrato.cont_numero)  as display"), \DB::raw("tbl_contrato.cont_estado as IdEstatus"))
	    ->where("tbl_contrato.cont_fechafin", ">", date('Y-m-d'))
	    ->where("tbl_contrato.cont_estado", "=", 1);
	    if ($lintLevelUser==6){
        	$this->data['selectContratos'] = $this->data['selectContratos']->where("tbl_contrato.entry_by_access","=",$lintIdUser);
		}elseif ($lintLevelUser==4){
			$this->data['selectContratos'] = $this->data['selectContratos']->where("tbl_contrato.admin_id","=",$lintIdUser);
		}
		$this->data['selectContratos']  = $this->data['selectContratos']->get();

		//Tipos KPIS
	    $this->data['selectTipos'] = \DB::table('tbl_kpis_tipos')
	    ->select(\DB::raw('tbl_kpis_tipos.IdTipo as value'), \DB::raw('tbl_kpis_tipos.Nombre as display'), "tbl_kpis_tipos.RangoSuperior", "tbl_kpis_tipos.RangoInferior", "tbl_kpis_tipos.IdEstatus", "tbl_kpis_tipos.Descripcion")
	    ->get();

		return view('tblkpi.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$row = $this->model->getRow($id);
		if($row)
		{
			$this->data['row'] =  $row;

			$this->data['id'] = $id;
			$this->data['access']		= $this->access;
			$this->data['setting'] 		= $this->info['setting'];
			$this->data['fields'] 		= \AjaxHelpers::fieldLang($this->info['config']['grid']);
			$this->data['subgrid']		= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
			return view('tblkpi.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_kpis ") as $column)
        {
			if( $column->Field != 'IdKpi')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));

			$sql = "INSERT INTO tbl_kpis (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_kpis WHERE IdKpi IN (".$toCopy.")";
			\DB::select($sql);
			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success')
			));

		} else {
			return response()->json(array(
				'status'=>'success',
				'message'=> 'Please select row to copy'
			));
		}


	}

	function postSave( Request $request, $id =0)
	{

		$lintIdUser = \Session::get('uid');
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_kpis');

			if (isset($data['RangoInferior']) && $data['RangoInferior']) {
				$data['RangoInferior'] = str_replace(".", "", $data['RangoInferior']);
				$data['RangoInferior'] = str_replace(",", ".", $data['RangoInferior']);
			}
			if (isset($data['RangoSuperior']) && $data['RangoSuperior'])  {
				$data['RangoSuperior'] = str_replace(".", "", $data['RangoSuperior']);
				$data['RangoSuperior'] = str_replace(",", ".", $data['RangoSuperior']);
			}

			if (!$request->input('IdKpi')){
				$data['entry_by'] = $lintIdUser;
				$data['created_at'] = date('Y-m-d');
			}else{
				$data['updated_by'] = $lintIdUser;
				$data['updated_at'] = date('Y-m-d');
			}
			$id = $this->model->insertRow($data , $request->input('IdKpi'));

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

		if($this->access['is_remove'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));
			die;

		}
		// delete multipe rows
		if(count($request->input('ids')) >=1)
		{
			$this->model->destroy($request->input('ids'));

			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success_delete')
			));
		} else {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));

		}

	}

	public static function display( )
	{
		$mode  = isset($_GET['view']) ? 'view' : 'default' ;
		$model  = new Tblkpi();
		$info = $model::makeInfo('tblkpi');

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
				return view('tblkpi.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdKpi' ,
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
			return view('tblkpi.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_kpis');
			 $this->model->insertRow($data , $request->input('IdKpi'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}


}
