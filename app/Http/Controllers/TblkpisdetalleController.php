<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Tblkpisdetalle;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class TblkpisdetalleController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'tblkpisdetalle';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Tblkpisdetalle();

		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'tblkpisdetalle',
			'pageUrl'			=>  url('tblkpisdetalle'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex( Request $request )
	{

		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['reg'] = $request->input('reg');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['area'] = $request->input('area');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['id'] = $request->input('id');

	    $lobjMyReports = new \MyReports($this->data);
	    $larrFilters = $lobjMyReports::getFilters(0); //El cero indica que no controla el filtro de contrato
		$this->data	= array_merge($this->data,$larrFilters);

		$this->data['access']		= $this->access;
		return view('tblkpisdetalle.index',$this->data);
	}

	public function postLoadinfo( Request $request)
	{

		//asignamos las viriables
		$this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['reg'] = $request->input('reg');
	    $this->data['area'] = $request->input('area');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['id'] = $request->input('id');

	    $lobjMyReports = new \MyReports($this->data);
	    $lobjMyReports::setOptions(array("title"=>""));
	    $larrResult["filter"] = $lobjMyReports::getFilters(0); //El cero indica que no controla el filtro de contrato
	    return response()->json($larrResult);

	}

	public function postData( Request $request)
	{

		$this->data['reg'] = $request->input('reg');
	    $this->data['seg'] = $request->input('seg');
	    $this->data['area'] = $request->input('area');
	    $this->data['ind'] = $request->input('ind');
	    $this->data['rep'] = $request->input('rep');
	    $this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['id'] = $request->input('id');

	    $lobjMyReports = new \MyReports($this->data);
	    $larrFilters = $lobjMyReports::getFilters(0);
		$this->data	= array_merge($this->data,$larrFilters);

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
		return view('tblkpisdetalle.table',$this->data);

	}

	public function getShowlist( Request $request){

	    $this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['id'] = $request->input('id');
	    $this->data['idestatus'] = $request->input('idestatus');

		$lintIdUser = \Session::get('uid');
		$lintGroupUser = \MySourcing::GroupUser($lintIdUser);
		$lintLevelUser = \MySourcing::LevelUser($lintIdUser);
		$sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
		$filter = "";

        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
        $filter .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';


		if ($this->data['year']) {
			$filter .= " AND YEAR(tbl_kpis_detalles.Fecha) = ".$this->data['year'];
		}else{
			$filter .= " AND YEAR(tbl_kpis_detalles.Fecha) = YEAR(NOW()) ";
		}

		if ($this->data['mes']) {
			$filter .= " AND MONTH(tbl_kpis_detalles.Fecha) = ".$this->data['mes'];
		}

		if ($this->data['id']) {
			$filter .= " AND tbl_kpis.contrato_id = ".$this->data['id'];
		}

		if ($this->data['idestatus']){
			if ($this->data['idestatus']==1){
				$filter .= " AND tbl_kpis_detalles.Puntaje IS NULL ";
			}else{
				$filter .= " AND tbl_kpis_detalles.Puntaje IS NOT NULL ";
			}
		}


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

			$id = $row->IdKpiDetalle;
			$larrResultTemp = array('id'=> ++$i,
								    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$id.'" /> '
								    );
			foreach ($this->info['config']['grid'] as $field) {
				if($field['view'] =='1') {
					$limited = isset($field['limited']) ? $field['limited'] :'';
					if (\SiteHelpers::filterColumn($limited )){
						$value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
						if ($field['field']=="Resultado") {
							if ($value) {
								$value = \MyFormats::FormatNumber($value).' %';
							}
						}
						if ( $field['field']=="ResultadoAjustado"){
							if ($value) {
								if ($value >= 0 && $value <= 40){
									$lstrColor = "#C0392B";
								}elseif ($value > 40 && $value <= 60){
									$lstrColor = "#D35400";
								}elseif ($value > 60 && $value <= 80){
									$lstrColor = "#F39C12";
								}elseif ($value > 80 && $value <= 100){
									$lstrColor = "#27AE60";
								}
								$value = '<span class="label label-primary" style="background-color:'.$lstrColor.';">'.\MyFormats::FormatNumber($value).' % </span>';
							}
						}
						$larrResultTemp[$field['field']] = $value;
					}
				}
			}
			$larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('tblkpisdetalle/update/'.$id).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-upload"></i></a></div>';
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
		if ($id){
			$filter = " AND tbl_kpis_detalles.IdKpiDetalle = ".$id;
		}else{
			$filter = " AND tbl_kpis_detalles.IdKpiDetalle = 0";
		}
		$params = array(
			'page'		=> '' ,
			'sort'		=> '' ,
			'order'		=> '',
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);

		$fetchMode = \DB::getFetchMode();
		\DB::setFetchMode(\PDO::FETCH_ASSOC);
		$row = $this->model->getRows($params);
		\DB::setFetchMode($fetchMode);

		if($row['rows'])
		{
			$this->data['row'] 		=  $row['rows'][0];
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_kpis_detalles');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);

		$this->data['id'] = $id;

	    $this->data['selectContratos'] = \DB::table('tbl_contrato')
	    ->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista")
	    ->select(\DB::raw('tbl_contrato.contrato_id as value'), \DB::raw("concat(tbl_contrato.cont_numero, ' ', tbl_contratistas.RazonSocial,' - ',tbl_contratistas.RUT)  as display"), \DB::raw("tbl_contrato.cont_estado as IdEstatus"));

		if ($this->data['id']){
			$lintIdContrato  = $this->data['row']['contrato_id'];
			$this->data['selectContratos'] = $this->data['selectContratos']->where("tbl_contrato.contrato_id","=",$lintIdContrato)->get();
			return view('tblkpisdetalle.form',$this->data);
		}else{

			$this->data['selectContratos'] = $this->data['selectContratos']->whereexists(function($query){
		    	$query->select(\DB::raw(1))
		    	      ->from("tbl_kpis")
		    	      ->whereraw("tbl_kpis.contrato_id = tbl_contrato.contrato_id")
		    	      ->whereraw("tbl_kpis.IdEstatus = 1");
		    });
		    if ($lintLevelUser==6){
	        	$this->data['selectContratos'] = $this->data['selectContratos']->where("tbl_contrato.entry_by_access","=",$lintIdUser);
			}elseif ($lintLevelUser==4){
				$this->data['selectContratos'] = $this->data['selectContratos']->where("tbl_contrato.admin_id","=",$lintIdUser);
			}
			$this->data['selectContratos']  = $this->data['selectContratos']->get();


			return view('tblkpisdetalle.new',$this->data);
		}

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
			return view('tblkpisdetalle.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_kpis_detalles ") as $column)
        {
			if( $column->Field != 'IdKpiDetalle')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_kpis_detalles (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_kpis_detalles WHERE IdKpiDetalle IN (".$toCopy.")";
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
			$data = $this->validatePost('tbl_kpis_detalles');

			//Remplazamos los valores de los puntajes
			if (isset($data['Puntaje']) && $data['Puntaje']) {
				$data['Puntaje'] = str_replace(".", "", $data['Puntaje']);
				$data['Puntaje'] = str_replace(",", ".", $data['Puntaje']);
			}
			if (isset($data['MetaSuperior']) && $data['MetaSuperior']) {
				$data['MetaSuperior'] = str_replace(".", "", $data['MetaSuperior']);
				$data['MetaSuperior'] = str_replace(",", ".", $data['MetaSuperior']);
			}
			if (isset($data['MetaInferior']) && $data['MetaInferior']) {
				$data['MetaInferior'] = str_replace(".", "", $data['MetaInferior']);
				$data['MetaInferior'] = str_replace(",", ".", $data['MetaInferior']);
			}

			if (!$request->input('IdKpiDetalle')){
				$data['entry_by'] = $lintIdUser;
				$data['created_at'] = date('Y-m-d');
			}else{
				$data['updated_by'] = $lintIdUser;
				$data['updated_at'] = date('Y-m-d');
			}

			//Buscamos cual es el tipo de KPI
			$lobjKpi = \DB::table('tbl_kpis')
			->where("tbl_kpis.IdKpi","=",$request->input('IdKpi'))
			->first();

			if ($request->input('IdKpiDetalle')){
				if ($lobjKpi){
					if ($lobjKpi->IdTipo == 1) { //Directo mayor igual
						$lintS = $data['Puntaje'];
						$lintC = $lobjKpi->RangoInferior;
						$lintD = $lobjKpi->RangoSuperior;
						$data['Resultado'] = ((($lintS)-($lintC))/(($lintD)-($lintC) )*100);
					}elseif ($lobjKpi->IdTipo == 2) { //Directo menor igual
						$lintS = $data['Puntaje'];
						$lintC = $lobjKpi->RangoSuperior;
						$lintD = $lobjKpi->RangoInferior;
						$data['Resultado'] = (100-(($lintS)-($lintC))/(($lintD)-($lintC) )*100);
					}elseif ($lobjKpi->IdTipo == 3) { //Rango mayor igual
						$lintS = $data['Puntaje'];
						$lintC = $lobjKpi->RangoInferior;
						$lintD = $data['MetaInferior']; //Lo recibe según el mes
						$lintE = $data['MetaSuperior']; //Lo recibe según el mes
						$data['Resultado'] = ((($lintS)-($lintC))/(($lintD)-($lintC) )*100);
					}elseif ($lobjKpi->IdTipo == 4) { //Rango menor igual
						$lintS = $data['Puntaje'];
						$lintC = $lobjKpi->RangoInferior;
						$lintD = $lobjKpi->RangoSuperior;
						$lintE = $data['MetaSuperior']; //Lo recibe según el mes
						$data['Resultado'] = (100-(($lintS)-($lintE))/(($lintC)-($lintE) )*100);
					}

					$data['RangoSuperior'] = $lobjKpi->RangoSuperior;
					$data['RangoInferior'] = $lobjKpi->RangoInferior;

				}

			}else{


				$lobjContrato = \DB::table('tbl_contrato')
				->select('tbl_contrato.cont_fechainicio', 'tbl_contrato.cont_fechafin')
				->join('tbl_kpis','tbl_kpis.contrato_id','=','tbl_contrato.contrato_id')
				->where('tbl_kpis.IdKpi','=', $request->input('IdKpi'))
				->first();

				$data['Fecha'] = date('Y-m-d',strtotime('01-'.str_replace('/','-',$data['Fecha'])));


				if ($data['Fecha'] > date('Y-m-d') ) {
					return response()->json(array(
						'message'	=> 'No se puede crear registro con fechas futuras',
						'status'	=> 'error'
					));
				}else if ($data['Fecha'] > $lobjContrato->cont_fechafin){
					return response()->json(array(
						'message'	=> 'No se puede crear registro con fechas posterior a la fecha de vigencia del contrato',
						'status'	=> 'error'
					));
				}else if ($data['Fecha'] < $lobjContrato->cont_fechainicio){
					return response()->json(array(
						'message'	=> 'No se puede crear registro con fechas anterior a la fecha de inicio del contrato',
						'status'	=> 'error'
					));
				}

				$lobjKpiDetalle = \DB::table("tbl_kpis_detalles")
									->where("tbl_kpis_detalles.IdKpi","=",$request->input('IdKpi'))
									->where("tbl_kpis_detalles.Fecha","=",$data['Fecha'])
									->get();
				if ($lobjKpiDetalle){
					return response()->json(array(
						'message'	=> 'Ya existe un registro creado para el periodo seleccionado',
						'status'	=> 'error'
					));
				}

			}

			$id = $this->model->insertRow($data , $request->input('IdKpiDetalle'));

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
		$model  = new Tblkpisdetalle();
		$info = $model::makeInfo('tblkpisdetalle');

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
				return view('tblkpisdetalle.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdKpiDetalle' ,
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
			return view('tblkpisdetalle.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_kpis_detalles');
			 $this->model->insertRow($data , $request->input('IdKpiDetalle'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}


}
