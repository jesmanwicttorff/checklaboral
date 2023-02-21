<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Requisito;
use App\Models\Requisitosdetalle;
use Illuminate\Http\Request;
use App\Models\Tipodocumentos;
use App\Models\Contratosservicios;
use App\Models\Contratosgruposespecificos;
use App\Models\Roles;
use App\Models\Rolesservicios;
use App\Library\MyDocuments;
use App\Library\MyRequirements;
use App\Models\Contratistas;
use App\Models\Contratos;
use App\Models\Personas;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class RequisitoController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'requisito';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Requisito();
		$this->modelview = new  \App\Models\Requisitosdetalleroles();
		$this->modelviewtwo = new  \App\Models\Requisitosareas();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'requisito',
			'pageUrl'			=>  url('requisito'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('requisito.index',$this->data);
	}

	public function postServicios(Request $request){

		$larrGrupoEspecifico = $request->input('idgrupoespecifico');
		if ($larrGrupoEspecifico){
			$lobjServicios = Contratosservicios::whereIn('idgrupoespecifico',$larrGrupoEspecifico)->select(\DB::raw('tbl_contratos_servicios.id as value'),
					     \DB::raw('tbl_contratos_servicios.name as display'))->get();
		}else{
			$lobjServicios = Contratosservicios::select(\DB::raw('tbl_contratos_servicios.id as value'),
					     \DB::raw('tbl_contratos_servicios.name as display'))->get();
		}

		return response()->json(["result"=>$lobjServicios]);

	}

	public function postRoles(Request $request){

		$larrGrupoEspecifico = $request->input('idgrupoespecifico');
		$larrServicios = $request->input('idservicio');

		if ($larrServicios){
			$lobjRoles = Roles::select(\DB::raw('tbl_roles.IdRol as value'),
					                   \DB::raw('tbl_roles.Descripción as display'))
			                    ->distinct()
			                    ->join('tbl_roles_servicios','tbl_roles_servicios.idrol','=','tbl_roles.IdRol')
			                    ->whereIn('tbl_roles_servicios.idservicio',$larrServicios)
			                    ->get();
		}else{
			if ($larrGrupoEspecifico){
				$lobjRoles = Roles::select(\DB::raw('tbl_roles.IdRol as value'),
					                   \DB::raw('tbl_roles.Descripción as display'))
			                    ->distinct()
			                    ->join('tbl_roles_servicios','tbl_roles_servicios.idrol','=','tbl_roles.IdRol')
			                    ->join('tbl_contratos_servicios','tbl_roles_servicios.idservicio','=','tbl_contratos_servicios.id')
			                    ->whereIn('tbl_contratos_servicios.idgrupoespecifico',$larrGrupoEspecifico)
			                    ->get();
			}else{
				$lobjRoles = Roles::select(\DB::raw('tbl_roles.IdRol as value'),
						     \DB::raw('tbl_roles.Descripción as display'))->get();
			}
		}

		return response()->json(["result"=>$lobjRoles]);

	}

	public function postData( Request $request)
	{
		$sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
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
			'limit'		=> (!is_null($request->input('rows')) ? filter_var($request->input('rows'),FILTER_VALIDATE_INT) : $this->info['setting']['perpage'] ) ,
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
		$pagination->setPath('requisito/data');

		$this->data['param']		= $params;
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
		$this->data['setting'] 		= $this->info['setting'];

		// Master detail link if any
		$this->data['subgrid']	= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
		// Render into template
		return view('requisito.table',$this->data);

	}


	function getUpdate(Request $request, $id = null)
	{

		if ($id){
			$lbolIsNew = false;
		}else{
			$lbolIsNew = true;
		}

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
			$this->data['row'] 		= $this->model->getColumnTable('tbl_requisitos');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );

		$this->data['selectEntidades'] = \DB::table('tbl_entidades')
		                                    ->select('IdEntidad as value',
		                                              'Entidad as display')
		                                    ->where('para_requisito','=',1)
		                                    ->get();

		$this->data['lbolIsNew'] = $lbolIsNew;

		if (!$lbolIsNew){
			$this->data['Entidad'] = $row->Entidades->Entidad;
			$this->data['TipoDocumento'] = $row->TipoDocumento->Descripcion;
		}

		$this->data['selectGrupoEspecifico'] = Contratosgruposespecificos::select(\DB::raw('tbl_contratos_grupos_especificos.id as value'),
					     \DB::raw('tbl_contratos_grupos_especificos.name as display'),
					     \DB::raw('case when tbl_requisitos_detalles.IdRequisito is null then false else true end as selectoption')
					      )
					     ->leftjoin('tbl_requisitos_detalles',function($table) use ($id) {
					     	$table->on('tbl_requisitos_detalles.IdRequisito',"=",\DB::raw("'".$id."'"))
					     	      ->on('tbl_requisitos_detalles.IdEntidad','=','tbl_contratos_grupos_especificos.id')
					     	      ->on('tbl_requisitos_detalles.Entidad','=',\DB::raw(1));
					     })
					     ->get();

		$this->data['selectServicios'] = Contratosservicios::select(\DB::raw('tbl_contratos_servicios.id as value'),
					     \DB::raw('tbl_contratos_servicios.name as display'),
					     \DB::raw('case when tbl_requisitos_detalles.IdRequisito is null then false else true end as selectoption'))
					     ->leftjoin('tbl_requisitos_detalles',function($table) use ($id) {
					     	$table->on('tbl_requisitos_detalles.IdRequisito',"=",\DB::raw("'".$id."'"))
					     	      ->on('tbl_requisitos_detalles.IdEntidad','=','tbl_contratos_servicios.id')
					     	      ->on('tbl_requisitos_detalles.Entidad','=',\DB::raw(2));
					     })
					     ->get();

		$this->data['selectRoles'] = Roles::select(\DB::raw('tbl_roles.IdRol as value'),
					     \DB::raw('tbl_roles.descripción as display'),
					     \DB::raw('case when tbl_requisitos_detalles.IdRequisito is null then false else true end as selectoption'))
					     ->leftjoin('tbl_requisitos_detalles',function($table) use ($id) {
					     	$table->on('tbl_requisitos_detalles.IdRequisito',"=",\DB::raw("'".$id."'"))
					     	      ->on('tbl_requisitos_detalles.IdEntidad','=','tbl_roles.IdRol')
					     	      ->on('tbl_requisitos_detalles.Entidad','=',\DB::raw(3));
					     })->get();

		$activos = \DB::table('tbl_activos')
							->select('IdActivo as value','Descripcion COLLATE utf8_general_ci AS display', \DB::raw('0 as selectoption'));
		$this->data['selectActivos'] =  \DB::table('tbl_activos')
								->leftjoin('tbl_activos_data','tbl_activos_data.IdActivo','=','tbl_activos.IdActivo')
								->leftjoin('tbl_requisitos_detalles','tbl_requisitos_detalles.IdEntidad','=','tbl_activos_data.IdActivo')
								->select('tbl_activos.IdActivo as value',\DB::raw('Descripcion COLLATE utf8_general_ci as display'),\DB::raw('case when IdActivoData IS NULL then FALSE ELSE TRUE end as selectoption'))
								->where('tbl_requisitos_detalles.IdRequisito',$id)
								->where('tbl_requisitos_detalles.Entidad',10)
								->groupBy('tbl_activos.IdActivo')
								->union($activos)
								->get();

		$larrAreasTrabajo = array( "title" => "Areas de trabajo", "master" => "contratos", "master_key" => "IdRequisito", "module" => "requisitosareas", "table" => "tbl_requisitos_detalles", "key" => "IdRequisito" );
		$this->data['subformtwo'] = $this->detailview($this->modelviewtwo ,  $larrAreasTrabajo ,$id );

		$this->data['id'] = $id;

		return view('requisito.form',$this->data);
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
			return view('requisito.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}

	function postTipodocumentos(Request $request) {

		$linIdEntidad = $request->input('identidad');
		$lobjTipoDocumento = TipoDocumentos::where('Entidad','=',$linIdEntidad)
		                     ->orderBy('Descripcion','asc')
		                     ->get();
		return response()->json(["result"=>$lobjTipoDocumento]);

	}
	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_requisitos ") as $column)
        {
			if( $column->Field != 'IdRequisito')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_requisitos (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_requisitos WHERE IdRequisito IN (".$toCopy.")";
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

		$bEntidad = $request->bulk_Entidad;
		$cont = $request->counter;
		if (count($bEntidad)>1){
			array_unshift($bEntidad,$cont[0]);
			$request['bulk_Entidad'] = $bEntidad;
		}

		if ($id==0){
			$lbolIsNew = 1;
		}else{
			$lbolIsNew = 0;
		}

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_requisitos');

			\DB::beginTransaction();

			$id = $this->model->insertRow($data , $request->input('IdRequisito'));

			//verificamos los grupos especificos
			$lobjRequisitosGrupos = Requisitosdetalle::where('idrequisito',$id)->where('entidad',1)->delete();
			$larrGruposEspecificos = $request->input('id_grupo_especifico');
			if ($larrGruposEspecificos){
				foreach ($larrGruposEspecificos as $value) {
					$lobjRequisitosGrupos = new Requisitosdetalle();
					$lobjRequisitosGrupos->IdRequisito = $id;
					$lobjRequisitosGrupos->Entidad = 1;
					$lobjRequisitosGrupos->IdEntidad = $value;
					$lobjRequisitosGrupos->save();
				}
			}

			//verificamos los servicios
			$lobjRequisitosServicios = Requisitosdetalle::where('idrequisito',$id)->where('entidad',2)->delete();
			$larrServicios = $request->input('id_servicios');
			if ($larrServicios){
				foreach ($larrServicios as $value) {
					$lobjRequisitosServicios = new Requisitosdetalle();
					$lobjRequisitosServicios->IdRequisito = $id;
					$lobjRequisitosServicios->Entidad = 2;
					$lobjRequisitosServicios->IdEntidad = $value;
					$lobjRequisitosServicios->save();
				}
			}

			//verificamos los roles
			if($request->Entidad==3){
				$lobjRequisitosGrupos = Requisitosdetalle::where('idrequisito',$id)->where('entidad',3)->delete();
				$larrGruposEspecificos = $request->input('id_roles');
				if ($larrGruposEspecificos){
					foreach ($larrGruposEspecificos as $value) {
						$lobjRequisitosGrupos = new Requisitosdetalle();
						$lobjRequisitosGrupos->IdRequisito = $id;
						$lobjRequisitosGrupos->Entidad = 3;
						$lobjRequisitosGrupos->IdEntidad = $value;
						$lobjRequisitosGrupos->save();
					}
				}
			}

			if($request->Entidad==10){
				$lobjRequisitosGrupos = Requisitosdetalle::where('idrequisito',$id)->where('entidad',10)->delete();
				$larrGruposEspecificos = $request->input('id_activos');
				if ($larrGruposEspecificos){
					foreach ($larrGruposEspecificos as $value) {
						$lobjRequisitosGrupos = new Requisitosdetalle();
						$lobjRequisitosGrupos->IdRequisito = $id;
						$lobjRequisitosGrupos->Entidad = 10;
						$lobjRequisitosGrupos->IdEntidad = $value;
						$lobjRequisitosGrupos->save();
					}
				}
			}



			if ($lbolIsNew){
				$lbolParaTodos = $request->input('Elevar');
				if ($lbolParaTodos){
					$lintCantidadMeses = $request->input('Meses');
					$lobjMyRequirements = new MyRequirements(null);
					$lobjMyRequirements::Load($id,null,null,$lintCantidadMeses);
				}
			}

			\DB::commit();

			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success')
				));

		} else {

			\DB::rollback();

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
			\DB::table('tbl_requisitos_detalles')->whereIn('IdRequisito',$request->input('ids'))->delete();
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
		$model  = new Requisito();
		$info = $model::makeInfo('requisito');

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
				return view('requisito.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdRequisito' ,
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
			return view('requisito.public.index',$data);
		}


	}


}
