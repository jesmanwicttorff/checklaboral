<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Busquedareportesexternos;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 

class BusquedareportesexternosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'busquedareportesexternos';
	static $per_page	= '10';
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new Busquedareportesexternos();
		
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'busquedareportesexternos',
			'pageUrl'			=>  url('busquedareportesexternos'),
			'return' 			=> 	self::returnUrl()	
		);		
				
	} 
	
	public function getIndex(Request $request)
	{
		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
				
		$this->data['access']		= $this->access;	

		//asignamos las viriables
	    $this->data['year'] = $request->input('year');
	    $this->data['mes'] = $request->input('mes');
	    $this->data['id'] = $request->input('id');

	    $lobjMyReports = new \MyReports($this->data);
	    $larrFilters = $lobjMyReports::getFilters(0);
		$this->data	= array_merge($this->data,$larrFilters);

		$this->data['tableGrid'] 	= $this->info['config']['grid'];

		return view('busquedareportesexternos.index',$this->data);

	}

	public function postLoadinfo(){

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
		$pagination->setPath('busquedareportesexternos/data');
		
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
		return view('busquedareportesexternos.table',$this->data);

	}

	function getShowlist(Request $request){

		// Get Query
	    $sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
	    $order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
	    
	    $lintId = $request->input('id');
	    $lintMonth = $request->input('month');
	    $lintYear = $request->input('year');

	    $filter = '';
	    if(!is_null($request->input('search')))
	    {
	      $search =   $this->buildSearch('maps');
	      $filter = $search['param'];
	      $this->data['search_map'] = $search['maps'];
	    }
	    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');

	    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
	    if ($lintLevelUser==6 || $lintLevelUser==15) {
	       $filter .= " AND (tbl_contrato.entry_by_access = ".$lintIdUser." OR tbl_contrato.contrato_id IN (select tbl_contratos_subcontratistas.contrato_id from tbl_contratistas inner join tbl_contratos_subcontratistas on tbl_contratos_subcontratistas.IdSubContratista = tbl_contratistas.IdContratista where entry_by_access = ".$lintIdUser.") )  ";
	    }else{
	       $filter .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';
	    }

	    if ($lintId != 0){
	    	$filter .= " AND tbl_contrato.contrato_id = ".$lintId.' ';	
	    }

	    if ($lintMonth != 0){
	    	$filter .= " AND dim_tiempo.Mes = ".$lintMonth.' ';
	    }

	    if ($lintYear != 0){
	    	$filter .= " AND dim_tiempo.Anio = ".$lintYear.' ';	
	    }

	    $params = array(
	      'page'    => '',
	      'limit'   => '',
	      'sort'    => $sort ,
	      'order'   => $order,
	      'params'  => $filter,
	      'global'  => (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
	    );
	    // Get Query
	    $results = $this->model->getRows( $params );

	    $larrResult = array();
	    $larrResultTemp = array();
	    $i = 0;

	    foreach ($results['rows'] as $row) {

	      $id = $row->id;

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
	      $onclick = "";
	      $lstrModule = "reportesexternosdetalle";
	      $lstrHtml = "";
	      if($this->access['is_detail'] ==1) {
	        if($this->info['setting']['view-method'] != 'expand'){
	          $onclick = " onclick=\"ajaxViewDetail('#".$lstrModule."',this.href); return false; \"" ;
	          if($this->info['setting']['view-method'] =='modal') {
	            $onclick = " onclick=\"SximoModal(this.href,'View Detail'); return false; \"" ;
	          }
	          $lstrHtml .= '<a href="'.\URL::to('reportesexternosdetalle/show/'.$id).'" '.$onclick.' class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_view').'"><i class="fa fa-search"></i></a>';
	        }
	      }

	      $larrResultTemp['action'] = $lstrHtml;
	      $larrResult[] = $larrResultTemp;
	    }

	    echo json_encode(array("data"=>$larrResult));
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
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_reportes_externos_detalle'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		
		$this->data['id'] = $id;

		return view('busquedareportesexternos.form',$this->data);
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
			return view('busquedareportesexternos.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));		
		}		
	}	


	function postCopy( Request $request)
	{
		
	    foreach(\DB::select("SHOW COLUMNS FROM tbl_reportes_externos_detalle ") as $column)
        {
			if( $column->Field != 'id')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));
			
					
			$sql = "INSERT INTO tbl_reportes_externos_detalle (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_reportes_externos_detalle WHERE id IN (".$toCopy.")";
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
		
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_reportes_externos_detalle');
			
			$id = $this->model->insertRow($data , $request->input('id'));
			
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
		$model  = new Busquedareportesexternos();
		$info = $model::makeInfo('busquedareportesexternos');

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
				return view('busquedareportesexternos.public.view',$data);
			} 

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'id' ,
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
			return view('busquedareportesexternos.public.index',$data);			
		}


	}

	function postSavepublic( Request $request)
	{
		
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_reportes_externos_detalle');		
			 $this->model->insertRow($data , $request->input('id'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}	
	
	}	
				

}