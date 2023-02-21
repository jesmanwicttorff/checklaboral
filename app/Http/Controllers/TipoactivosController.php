<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Tipoactivos;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class TipoactivosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'tipoactivos';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Tipoactivos();
		$this->modelview = new  \App\Models\Activosdetalle();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'tipoactivos',
			'pageUrl'			=>  url('tipoactivos'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('tipoactivos.index',$this->data);
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
		$pagination->setPath('tipoactivos/data');

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
		return view('tipoactivos.table',$this->data);

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
			$this->data['row'] 		=  $row;
			$this->data['tipodatos']=  \DB::table('tbl_activos_detalle')
			                              ->leftJoin("tbl_activos_detalles_listas", 'tbl_activos_detalle.IdActivoDetalle', '=', 'tbl_activos_detalles_listas.IdActivoDetalle')
			                              ->where('tbl_activos_detalle.IdActivo',$row['IdActivo'])
			                              ->orderBy("tbl_activos_detalle.OrdenForm","asc")
			                              ->orderBy("tbl_activos_detalle.IdActivoDetalle","asc")
			                              ->get();

			   $this->data['comprueba']=  \DB::table('tbl_activos_data')
	                              ->where('tbl_activos_data.IdActivo',$row['IdActivo'])
	                              ->get();
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_activos');
			$this->data['tipovalor']=  array();
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );

		$this->data['datos']=  \DB::table('tbl_activos_detalle')
                              ->where('tbl_activos_detalle.IdActivo',$row['IdActivo'])
                              ->where('IdEstatus',"=",1)
                              ->orderBy("tbl_activos_detalle.OrdenForm","asc")
                              ->orderBy("tbl_activos_detalle.IdActivoDetalle","asc")
                              ->get();

		$this->data['id'] = $id;

		return view('tipoactivos.form',$this->data);
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
			return view('tipoactivos.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}

	function getShowlist(Request $request){

        // Get Query
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
		$params = array(
			'page'		=> '',
			'limit'		=> '',
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query
		$results = $this->model->getRows( $params );

		$larrResult = array();
		$larrResultTemp = array();
		$i = 0;

		foreach ($results['rows'] as $row) {

			$id = $row->{$this->info['key']};

			$larrResultTemp = array('id'=> ++$i,
								    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$row->{$this->info['key']}.'" /> '
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
			/*$larrResultTemp['action'] = \AjaxHelpers::buttonAction($this->module,$this->access,$id ,$this->info['setting'],3).\AjaxHelpers::buttonActionInline($row->{$this->info['key']},$this->info['key']);*/
			$larrResultTemp['action'] = '<a href="'.\URL::to('tipoactivos/update/'.$id).'" onclick="ajaxViewDetail(\'#tipoactivos\',this.href); return false; " class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-edit"></i></a>';
			$larrResult[] = $larrResultTemp;
		}

		echo json_encode(array("data"=>$larrResult));

    }


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_activos ") as $column)
        {
			if( $column->Field != 'IdActivo')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_activos (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_activos WHERE IdActivo IN (".$toCopy.")";
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
//\Log::info($request); die;
		$lobjTipoValor = json_decode($request->activosdetalle);
		$counter =  $request->counter;
		$etiqueta = $request->bulk_Etiqueta;
		$tipo = $request->bulk_Tipo;
		$EntryBy = \Session::get('uid');
		$requerido = $request->bulk_Requerido;
		$unico = $request->bulk_Unico;

		$DocV = $request->IdActivoDetalle;


		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_activos');

			if (strlen($request->IconoActivo)==0){
			$data['IconoActivo'] = "fa fa-circle";
		}

		$idEntidadActivos = \DB::table('tbl_entidades')->where('Entidad','Activos')->first()->IdEntidad;
		$data['IdEntidadActivo'] = $idEntidadActivos;
		$data['entry_by'] = $EntryBy;

			$id = $this->model->insertRow($data , $request->input('IdActivo'));

			$tipoV =  \DB::table('tbl_activos_detalle')
			->select('IdActivoDetalle')
			->where('IdActivo', '=', $request->IdActivo)
		            ->orderBy("tbl_activos_detalle.OrdenForm","asc")
		            ->orderBy("tbl_activos_detalle.IdActivoDetalle","asc")
			->get();

			if (count($tipoV)>0 ){
				foreach ($tipoV as $value){
					$array[] = $value->IdActivoDetalle;
				}

				if (!(empty($DocV))){
					foreach ($array as $valor) {
						if (!(in_array($valor, $DocV))){
							//\DB::table('tbl_activos_detalles_listas')->where('IdActivoDetalle', '=', $valor)->delete();
							\DB::table('tbl_activos_detalle')
							->where('IdActivoDetalle', '=', $valor)
							->update(["IdEstatus"=>0]);
						}
					}
				}
			}

			for($i = 0; $i<count($counter); $i++){
				$lintIdInsert = $i+1;
				if ((strlen($etiqueta[$i])!=0) && (strlen($tipo[$i])!=0) && (strlen($requerido[$i])!=0) && (strlen($unico[$i])!=0)){
					if (!(empty($DocV[$i]))){
						\DB::table('tbl_activos_detalle')
						   ->where('IdActivoDetalle', $DocV[$i])
						   ->update(['Etiqueta' => $etiqueta[$i],
						   	         'Tipo' => $tipo[$i],
						   	         'Requerido' => $requerido[$i],
						   	         'Unico' => $unico[$i],
						   	         'OrdenForm' => $i]);
						if ($tipo[$i]=="Radio" || $tipo[$i]=="CheckBox" || $tipo[$i]=="Select Option" ){
	 						if (isset($lobjTipoValor->{$lintIdInsert})) {
	 						  \DB::table('tbl_activos_detalles_listas')->where('IdActivoDetalle', '=', $DocV[$i])->delete();
	 						  $is = 0;
	 						  foreach ($lobjTipoValor->{$lintIdInsert}->{"valores"} as $value) {
	 						  	if ($value!="" || $lobjTipoValor->{$lintIdInsert}->{"display"}[$is] != ""){
	 						  	  $IdDocV = \DB::table('tbl_activos_detalles_listas')->insertGetId(
							  	  ['IdActivoDetalle' => $DocV[$i],
							  	   'Valor' => $value,
							  	   'Etiqueta' => $lobjTipoValor->{$lintIdInsert}->{"display"}[$is] ]);
	 						  	}
	 						  	$is += 1;
	 						  }
							}
	 					}
					}
					else{
					$IdDocV = \DB::table('tbl_activos_detalle')->insertGetId(
					['IdActivo' => $id, 'Etiqueta' => $etiqueta[$i], 'Tipo' => $tipo[$i], 'Requerido' => $requerido[$i], 'Unico' => $unico[$i], 'OrdenForm'=>$i ]);
	 					if ($tipo[$i]=="Radio" || $tipo[$i]=="CheckBox" || $tipo[$i]=="Select Option" ){
	 						if (isset($lobjTipoValor->{$lintIdInsert})) {
	 						  \DB::table('tbl_activos_detalles_listas')->where('IdActivoDetalle', '=', $IdDocV)->delete();
	 						  $is = 0;
	 						  foreach ($lobjTipoValor->{$lintIdInsert}->{"valores"} as $value) {
	 						  	if ($value!="" || $lobjTipoValor->{$lintIdInsert}->{"display"}[$is] != ""){
	 						  	  $IdDocD = \DB::table('tbl_activos_detalles_listas')->insertGetId(
							  	  ['IdActivoDetalle' => $IdDocV,
							  	   'Valor' => $value,
							  	   'Etiqueta' => $lobjTipoValor->{$lintIdInsert}->{"display"}[$is] ]);
	 						  	}
	 						  	$is += 1;
	 						  }
							}
	 					}
					}
				}

			}


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
			\DB::table('tbl_activos_detalle')->whereIn('IdActivo',$request->input('ids'))->delete();
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
		$model  = new Tipoactivos();
		$info = $model::makeInfo('tipoactivos');

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
				return view('tipoactivos.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdActivo' ,
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
			return view('tipoactivos.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_activos');
			 $this->model->insertRow($data , $request->input('IdActivo'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}


}
